<?php
/**
 * @file
 *   DrooPHP_Method_Ers97 class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Method_Ers97
 *   Method for counting votes according to the Electoral Reform Society 1997
 *   standard (ERS97) for single transferable vote (STV) elections.
 * @extends
 *   DrooPHP_Method
 */
class DrooPHP_Method_Ers97 extends DrooPHP_Method {

  /**
   * Calculate the total active vote: "The sum of the votes credited to all
   * continuing candidates, plus any votes awaiting transfer."
   *
   * See http://www.cix.co.uk/~rosenstiel/stvrules/details.htm#Totalactivevote
   *
   * @return int
   */
  public function calculateActiveVote() {
    $candidates = $this->election->candidates;
    $active_vote = 0;
    foreach ($candidates as $candidate) {
      if ($candidate->state === DrooPHP_Candidate::STATE_HOPEFUL) {
        $active_vote += $candidate->votes;
        // @todo add "plus any votes awaiting transfer"
      }
    }
    return $active_vote;
  }

  /**
   * @see parent::logRound()
   */
  public function logRound($round) {
    parent::logRound($round);
    $log = &$this->rounds[$round];
    $log['active_vote'] = $this->calculateActiveVote();
    var_dump($log);
  }

  /** @todo */
  public function run($round = 1) {
    parent::run($round);
    $num_seats = $this->election->num_seats;
    $quota = $this->quota;
    $next_round = $round + 1;
    $someone_elected = FALSE;
    $active_vote = $this->calculateActiveVote();
    $remaining = array();
    foreach ($this->election->candidates as $cid => $candidate) {
      if ($candidate->state !== DrooPHP_Candidate::STATE_HOPEFUL) {
        // Ignore elected, withdrawn, or defeated candidates.
        continue;
      }
      if ($candidate->votes >= $quota || $candidate->votes > ($active_vote / ($num_seats - $this->num_elected + 1))) {
        // The candidate is now elected.
        echo "Elected $cid\n"; // debugging
        $candidate->state = DrooPHP_Candidate::STATE_ELECTED;
        $someone_elected = TRUE;
        $this->num_elected++;
        // Calculate the candidate's surplus, and transfer votes for the next round.
        $surplus = $candidate->votes - $quota;
        $candidate->log("Elected in round $round.");
        if ($surplus > 0) {
          $this->transferVotes($cid, $surplus, $next_round);
          $candidate->log("A surplus of $surplus votes was transferred to other candidates for round $next_round.");
        }
      }
      else {
        // If the candidate hasn't been elected, add number of votes to $remaining so a comparison can be made for elimination.
        $remaining[$cid] = $candidate->votes;
      }
    }
    // If no-one has been elected in this round, eliminate the candidate with the fewest votes.
    if (!$someone_elected) {
      $to_eliminate = NULL;
      foreach ($remaining as $cid => $votes) {
        if (!isset($last) || $votes < $last) {
          $last = $votes;
          $to_eliminate = $cid;
        }
      }
      if ($to_eliminate !== NULL) {
        $cid = $to_eliminate;
        $votes = $remaining[$cid];
        echo "Defeated $cid\n"; // debugging
        $candidate = $this->election->getCandidate($cid);
        $candidate->state = DrooPHP_Candidate::STATE_DEFEATED;
        $candidate->log("Defeated in round $round.");
        if ($votes > 0) {
          $this->transferVotes($cid, $votes, $next_round);
          $candidate->log("$votes votes were transferred from defeated candidate '$cid' to other candidates for round $next_round.");
        }
      }
    }
    // Proceed to the next round or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
    }
    else if ($round >= $this->count->getOption('maxRounds')) {
      throw new Exception('Maximum number of rounds reached before completing the count.');
      return FALSE;
    }
    else {
      $this->run($next_round);
    }
  }

  /**
   * Transfer the votes from a successful candidate to the other hopeful ones.
   *
   * @param mixed $from_cid
   * @param int $surplus
   * @param int $round
   */
  public function transferVotes($from_cid, $surplus, $round) {
    echo "Attempting transfer from $from_cid\n"; // debugging

    // @todo
  }

  /**
   * Calculate the minimum number of votes a candidate needs in order to be
   * elected.
   *
   * According to the Electoral Reform Society, this should be done by "dividing
   * the total valid vote by one more than the number of places to be filled.
   * Take the division to two decimal places. If the result is exact that is the
   * quota. Otherwise ignore the remainder, and add 0.01".
   *
   * @return float
   */
  protected function _calculateQuota() {
    $election = $this->election;
    $num = $election->num_valid_ballots / ($election->num_seats + 1);
    $quota = ceil($num * 100) / 100;
    $this->quota = $quota;
    return $quota;
  }

}
