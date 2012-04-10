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
  public function getActiveVote() {
    $candidates = $this->count->election->candidates;
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
   * @see parent::logStage()
   */
  public function logStage($stage) {
    parent::logStage($stage);
    $log = &$this->stages[$stage];
    $log['active_vote'] = $this->getActiveVote();
    $log['surpluses'] = $this->getSurpluses();
  }

  /** @todo */
  public function run($stage = 1) {

    $election = $this->count->election;

    // First stage. // ERS97 5.1
    if ($stage == 1) {
      // Count the first preference votes and add them to each candidate // ERS97 5.1.4
      $total = 0;
      foreach ($election->ballots as $ballot) {
        $candidate = $election->getCandidate($ballot->ranking[1]);
        $candidate->votes += $ballot->value;
        $total += $ballot->value;
      }
      // Check that the total is the same as the total valid vote. // ERS97 5.1.5 // @todo this is unnecessary
      if ($total != $election->num_valid_ballots) {
        throw new DrooPHP_Exception('Total votes in stage 1 not equal to the total valid vote.');
      }
    }

    // Elect candidates // ERS97 5.1.7 // ERS97 5.3.13 // ERS97 5.4.9
    /*
      Considering each candidate in turn in descending order of their votes, deem
      elected and candidate whose vote equals or exceeds:
        (a) the quota, or
        (b) (on very rare occasions, where this is less than the quota), the total
            active vote, divided by one more than the number of places not yet filled.
      up to the number of places to be filled, subject to paragraph 5.6.2. // ERS97 5.6.2 refers to ties
     */
    $candidates = $this->orderCandidates(); // sort into descending order of votes
    $active_vote = $this->getActiveVote();
    foreach ($candidates as $cid => $candidate) {
      $num_vacancies = $election->num_seats - $election->num_filled_seats;
      if ($num_vacancies == 0) {
        break;
      }
      if ($candidate->state === DrooPHP_Candidate::STATE_HOPEFUL) {
        if ($candidate->votes >= $this->quota || $candidate->votes >= ($active_vote / ($num_vacancies + 1))) {
          // The candidate is now elected.
          $candidate->state = DrooPHP_Candidate::STATE_ELECTED;
          $candidate->log("Elected in stage $stage.");
          $election->num_filled_seats++;
          if ($candidate->votes > $this->quota) {
            $candidate->surplus = $candidate->votes - $this->quota;
          }
        }
      }
    }

    if ($stage == 1) {
      // That completes the first stage of the count. // ERS97 5.1.8
      $this->logStage($stage);
      $stage++;
    }

    // If one or more candidates have surpluses, the largest of these should now be transferred. // ERS97 5.2.2
    $surpluses = $this->getSurpluses();
    if (!empty($surpluses)) {
    }

    // @todo elimination
    // @todo transfer surplus from withdrawn candidates
    // @todo transfer surplus after elimination

    return;

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
    }
    else if ($stage >= $this->count->getOption('maxStages')) {
      throw new Exception('Maximum number of stages reached before completing the count.');
      return FALSE;
    }
    else {
      $this->logStage($stage);
      $this->run($stage + 1);
    }
  }

  /**
   * Return an array of candidates in descending order of their votes. // ERS97 5.1.7
   *
   * @return array
   *   Array of DrooPHP_Candidate objects, keyed by candidate ID.
   */
  public function orderCandidates() {
    $election = $this->count->election;
    $candidates_votes = array(); // array of vote amounts keyed by candidate ID
    foreach ($election->candidates as $cid => $candidate) {
      $candidates_votes[$cid] = $candidate->votes;
    }
    arsort($candidates_votes, SORT_NUMERIC);
    $candidates = array();
    foreach ($candidates_votes as $cid => $votes) {
      $candidates[$cid] = $election->candidates[$cid];
    }
    return $candidates;
  }

  /**
   * Return an array of candidates' surpluses in descending order of size. // ERS97 5.2.3
   *
   * @return array
   *   Array of surpluses (floats), keyed by candidate ID.
   */
  public function getSurpluses() {
    $candidates = $this->count->election->candidates;
    $surpluses = array();
    foreach ($candidates as $cid => $candidate) {
      if ($candidate->surplus > 0) {
        $surpluses[$cid] = $candidate->surplus;
      }
    }
    arsort($surpluses, SORT_NUMERIC);
    return $surpluses;
  }

  /**
   * Transfer the votes from a successful candidate to the other hopeful ones. // ERS97 5.3 // ERS97 5.4
   *
   * @param mixed $from_cid
   * @param int $surplus
   * @param int $stage
   */
  public function transferVotes($from_cid, $surplus, $stage) {
    echo "Attempting transfer from $from_cid\n"; // debugging

    // @todo
  }

  /**
   * Calculate the minimum number of votes a candidate needs in order to be
   * elected. // ERS97 5.1.6 // ERS97 5.4.8 // ERS97 6.14
   *
   * According to ERS97 5.1.6, this should be done by "dividing
   * the total valid vote by one more than the number of places to be filled.
   * Take the division to two decimal places. If the result is exact that is the
   * quota. Otherwise ignore the remainder, and add 0.01".
   *
   * @return float
   */
  protected function calculateQuota() {
    $election = $this->count->election;
    $num = $election->num_valid_ballots / ($election->num_seats + 1);
    $quota = ceil($num * 100) / 100;
    $this->quota = $quota;
    return $quota;
  }

}
