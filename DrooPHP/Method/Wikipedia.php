<?php
/**
 * @file
 *   DrooPHP_Method_Wikipedia class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Method_Wikipedia
 *   Method for counting votes described in the Wikipedia article "Single
 *   transferable vote".
 * @extends
 *   DrooPHP_Method
 */
class DrooPHP_Method_Wikipedia extends DrooPHP_Method {

  /**
   * Run a count.
   *
   * From Wikipedia:
   *   An STV election proceeds according to the following steps:
   *     1. Any candidate who has reached or exceeded the quota is declared
   *        elected.
   *     2. If a candidate has more votes than the quota, that candidate's
   *        surplus votes are transferred to other candidates. Votes that would
   *        have gone to the winner instead go to the next preference listed on
   *        their ballot.
   *     3. If no one new meets the quota, the candidate with the fewest votes
   *        is eliminated and that candidate's votes are transferred.
   *     4. This process repeats until either a winner is found for every seat
   *        or there are as many seats as remaining candidates.
   *
   * @see http://en.wikipedia.org/wiki/Single_transferable_vote#Finding_the_winners
   *
   * @param int $stage
   *
   * @return bool $success
   */
  public function run($stage = 1) {

    $election = $this->count->election;

    // First stage.
    if ($stage == 1) {
      // Count the first preference votes and add them to each candidate
      $total = 0;
      foreach ($election->ballots as $ballot) {
        $first_preference = $ballot->ranking[1];
        if (is_array($first_preference)) {
          // Deal with equal rankings (this can be dealt with as an edge case).
          $num = count($first_preference);
          foreach ($first_preference as $cid) {
            $candidate = $election->getCandidate($cid);
            $candidate->votes += (1 / $num) * $ballot->value;
            $total += (1 / $num) * $ballot->value;
          }
        }
        else {
          $candidate = $election->getCandidate($first_preference);
          $candidate->votes += $ballot->value;
          $total += $ballot->value;
        }
        $ballot->last_used_level = 1;
      }

      $this->logStage('Start of count');

      // If there are any withdrawn candidates, transfer their votes.
      $withdrawn = $election->getCandidatesByState(DrooPHP_Candidate::STATE_WITHDRAWN);
      foreach ($withdrawn as $candidate) {
        if ($candidate->votes) {
          $candidate->log(sprintf('Withdrawn candidate: all %d votes will be transferred.', $candidate->votes));
          $this->transferVotes($candidate->votes, $candidate, $stage);
        }
      }
    }

    // Elect candidates
    $hopefuls = $election->getCandidatesByState(DrooPHP_Candidate::STATE_HOPEFUL);
    $quota = $this->quota;
    $anyone_elected = FALSE;
    foreach ($hopefuls as $candidate) {
      // A candidate is elected if their votes equal or exceed the quota.
      if ($candidate->votes >= $quota) {
        $candidate->state = DrooPHP_Candidate::STATE_ELECTED;
        $election->num_filled_seats++;
        $anyone_elected = TRUE;
        $surplus = $candidate->votes - $quota;
        $candidate->log(sprintf('Elected at stage %d, with a surplus of %s votes.', $stage, $surplus));
        if ($surplus > 0 && !$this->isComplete()) {
          $this->transferVotes($surplus, $candidate, $stage);
        }
      }
    }

    // Eliminate candidates.
    // "If no one new meets the quota, the candidate with the fewest votes is eliminated and that candidate's votes are transferred."
    if (!$anyone_elected) {
      $candidate = $this->findDefeatableCandidate();
      if ($candidate) {
        $candidate->state = DrooPHP_Candidate::STATE_DEFEATED;
        $candidate->log(sprintf('Defeated at stage %d, with %s votes.', $stage, $candidate->votes));
        if ($candidate->votes && !$this->isComplete()) {
          $this->transferVotes($candidate->votes, $candidate, $stage);
          if ($this->count->options['allow_equal']) {
            $candidate->votes = round($candidate->votes, 0); // compensate for rounding errors in transfer with equal rankings
          }
        }
      }
    }

    $hopefuls = $election->getCandidatesByState(DrooPHP_Candidate::STATE_HOPEFUL);
    // If there are as many seats as remaining candidates, all the remaining candidates are elected.
    if (count($hopefuls) == $this->getNumVacancies()) {
      foreach ($hopefuls as $candidate) {
        $candidate->state = DrooPHP_Candidate::STATE_ELECTED;
        $candidate->log(sprintf('Elected at stage %d, by default.', $stage));
        $election->num_filled_seats++;
      }
    }
    else if ($this->getNumVacancies() == 0) {
      // If there are no remaining vacancies, all the remaining candidates are defeated.
      foreach ($hopefuls as $candidate) {
        $candidate->state = DrooPHP_Candidate::STATE_DEFEATED;
        $candidate->log(sprintf('Defeated at stage %d, by default.', $stage));
      }
    }

    $this->logStage("End of stage $stage");

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
    }
    else if ($stage >= $this->count->getOption('maxStages')) {
      throw new Exception('Maximum number of stages reached before completing the count.');
      return FALSE;
    }
    else {
      return $this->run($stage + 1);
    }
  }

  /**
   * Return the hopeful candidate with the fewest votes.
   */
  public function findDefeatableCandidate() {
    $election = $this->count->election;
    $hopefuls = $election->getCandidatesByState(DrooPHP_Candidate::STATE_HOPEFUL);
    // Candidates can only be defeated if sufficient candidates remain to fill all the vacancies.
    if (count($hopefuls) <= $this->getNumVacancies()) {
      return FALSE;
    }
    $defeatable = FALSE;
    foreach ($hopefuls as $cid => $candidate) {
      if ($defeatable === FALSE || $candidate->votes < $defeatable->votes) {
        $defeatable = $candidate;
      }
    }
    return $defeatable;
  }

  /**
   * Transfer a candidate's votes or surplus to other hopefuls.
   *
   * @param float $num_to_transfer
   * @param DrooPHP_Candidate $from_candidate.
   * @param int $stage
   */
  public function transferVotes($num_to_transfer, DrooPHP_Candidate $from_candidate, $stage) {
    $election = $this->count->election;
    $hopefuls = $election->getCandidatesByState(DrooPHP_Candidate::STATE_HOPEFUL);
    $votes = array();
    foreach ($election->ballots as $ballot) {
      $ranking = $ballot->ranking;
      $last_used_level = $ballot->last_used_level;
      if (!isset($ranking[$last_used_level]) || $ranking[$last_used_level] != $from_candidate->cid) {
        // Not a relevant ballot.
        continue;
      }
      if (!isset($ballot->ranking[$last_used_level + 1])) {
        // No preference given. This is an exhausted ballot.
        $election->num_exhausted_ballots++;
        continue;
      }
      $to_cids = $ballot->ranking[$last_used_level + 1];
      if (!is_array($to_cids)) {
        $to_cids = array($to_cids); // this is to deal with equal rankings
      }
      $count_to_cids = count($to_cids);
      foreach ($to_cids as $to_cid) {
        if (!isset($hopefuls[$to_cid])) {
          // The next preference candidate is not in the running.
          // @todo check if this is valid
          continue;
        }
        if (!isset($votes[$to_cid])) {
          $votes[$to_cid] = 0;
        }
        $value = (1 / $count_to_cids) * $ballot->value;
        $votes[$to_cid] += $value;
        $ballot->last_used_level = $last_used_level + 1;
      }
    }
    // To convert this into a ratio, find the total number of votes found at $to_preference_level.
    $total_votes = array_sum($votes);
    if ($total_votes == 0) {
      // No transfers to be made.
      return;
    }
    // Run the transfer.
    foreach ($votes as $to_cid => $num_votes) {
      $amount = ($num_to_transfer / $total_votes) * $num_votes;
      $to_candidate = $hopefuls[$to_cid];
      $from_candidate->votes -= $amount;
      $to_candidate->votes += $amount;
      $from_candidate->log(sprintf('Transferred %s votes to %s.', $amount, $to_candidate->name, $stage));
      $to_candidate->log(sprintf('Received %s votes from %s at stage %d.', $amount, $from_candidate->name, $stage));
    }
  }

}
