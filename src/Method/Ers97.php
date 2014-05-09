<?php
/**
 * @file
 * Method for counting votes according to the Electoral Reform Society 1997
 * standard (ERS97) for single transferable vote (STV) elections.
 *
 * @todo This is incomplete and broken.
 */

namespace DrooPHP\Method;

use DrooPHP\CandidateInterface;
use DrooPHP\Exception\CountException;

class Ers97 extends MethodBase {

  /**
   * Each candidate that exists in this array has been determined to have more
   * votes than the next candidate, at the earliest stage in the count at which
   * those candidates had unequal votes. If two candidates have had equal votes
   * throughout the election they will not appear in this array.
   *
   * See ERS97 5.2.3, ERS97 5.2.5, ERS97 5.6.2.
   */
  public $unambiguous_order = [];

  /**
   * @{inheritdoc}
   */
  public function getName() {
    return 'ERS 1997';
  }

  /**
   * Calculate the total active vote: "The sum of the votes credited to all
   * continuing candidates, plus any votes awaiting transfer."
   *
   * See http://www.cix.co.uk/~rosenstiel/stvrules/details.htm#Totalactivevote
   *
   * @return int
   */
  public function getActiveVote() {
    $active_vote = 0;
    foreach ($this->getElection()->getCandidates() as $candidate) {
      switch ($candidate->getState()) {
        case CandidateInterface::STATE_ELECTED:
          $active_vote += $candidate->getSurplus();
          break;
        case CandidateInterface::STATE_HOPEFUL:
        case CandidateInterface::STATE_DEFEATED:
        case CandidateInterface::STATE_WITHDRAWN:
          $active_vote += $candidate->getVotes();
          break;
      }
    }
    return $active_vote;
  }

  /**
   * Overrides parent::logStage().
   */
  public function logStage($stage) {
    parent::logStage($stage);
    $log = & $this->stages[$stage];
    $log['surpluses'] = $this->getSurpluses();
    $log['active_vote'] = $this->getActiveVote();
  }

  /**
   * Overrides parent::run().
   */
  public function run($stage = 1) {

    $election = $this->getElection();

    // Log the current status of the count (i.e. the status reached at the end of the previous stage).
    if ($stage > 1) {
      $this->logStage($stage - 1);
    }

    // First stage. // ERS97 5.1
    if ($stage == 1) {
      $this->calculateQuota();
      // Count the first preference votes and add them to each candidate // ERS97 5.1.4
      $total = 0;
      foreach ($election->getBallots() as $ballot) {
        $first_preference = $ballot->getRanking(1);
        // Deal with equal rankings (probably not permitted in ERS97 but this can be dealt with as an edge case).
        $num = count($first_preference);
        foreach ($first_preference as $cid) {
          $candidate = $election->getCandidate($cid);
          $candidate->setVotes((1 / $num) * $ballot->getValue(), TRUE);
          $total += (1 / $num) * $ballot->getValue();
        }
      }
      // Check that the total is the same as the total valid vote. // ERS97 5.1.5 // @todo this is unnecessary
      if ($total != $election->getNumValidBallots()) {
        throw new CountException('Total votes in stage 1 not equal to the total valid vote.');
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
    $candidates = $this->getCandidatesOrder(); // sort into descending order of votes
    $active_vote = $this->getActiveVote();
    $quota = $this->getQuota();
    $anyone_elected = FALSE;
    foreach ($candidates as $candidate) {
      $num_vacancies = $this->getNumVacancies();
      if ($num_vacancies == 0) {
        // If all seats are filled, the election has finished.
        return TRUE;
      }
      if ($candidate->getState() === CandidateInterface::STATE_HOPEFUL) {
        if ($candidate->getVotes() >= $quota || $candidate->getVotes() >= ($active_vote / ($num_vacancies + 1))) {
          // The candidate is now elected.
          $candidate->setState(CandidateInterface::STATE_ELECTED);
          $anyone_elected = TRUE;
          if ($candidate->getVotes() > $quota) {
            $candidate->setSurplus($candidate->getVotes() - $quota);
            $this->logChange($candidate, sprintf('Elected at stage %d with a surplus of %d votes.', $stage, $candidate->getSurplus()), $stage);
          }
          else {
            $this->logChange($candidate, sprintf('Elected at stage %d.', $stage), $stage);
          }
        }
      }
    }

    if ($stage == 1) {
      // If we're on the first stage, it's now complete, the next actions are part of stage 2. // ERS97 5.1.8
      return $this->run($stage + 1);
    }

    if ($anyone_elected) {
      // If anyone has been elected in this stage, then it's now complete.
      return $this->run($stage + 1);
    }

    // If one or more candidates have surpluses, the largest of these should now be transferred. // ERS97 5.2.2
    $surpluses = $this->getSurpluses();
    if (!empty($surpluses)) {
      // @todo work out what to do with the deferment rules in ERS97 5.2.2
      /*
                  $candidate_fewest_votes_diff = 0;
                  foreach (array_slice($candidates, -2) as $cid => $candidate) {
                      if (isset($candidate_second_fewest_votes)) {
                          $candidate_fewest_votes_diff = $candidate_second_fewest_votes - $candidate->getVotes();
                          break;
                      }
                      $candidates_second_fewest_votes = $candidate->getVotes();
                  }
                  $total_surplus = array_sum($surpluses);
      */
      // @todo transfer
      // The transfer of a surplus constitutes a stage in the count. // ERS97 5.2.4
      //return $this->run($stage + 1); // not ready to loop yet
    }

    // @todo eliminate candidates
    // @todo transfer after elimination
    // @todo transfer from withdrawn candidates
    //$this->defeatCandidates();

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return $this->getResult();
    }
    elseif ($stage >= $this->getConfig()->getOption('max_stages')) {
      throw new CountException('Maximum number of stages reached before completing the count.');
    }
    return $this->run($stage + 1);
  }

  /**
   * Get candidates in descending order of their votes. // ERS97 5.1.7
   *
   * @return CandidateInterface[]
   *   Array of Candidate objects, keyed by candidate ID.
   */
  public function getCandidatesOrder() {
    $candidates_votes = []; // array of vote amounts keyed by candidate ID
    foreach ($this->getElection()->getCandidates() as $cid => $candidate) {
      $candidates_votes[$cid] = $candidate->getVotes();
    }
    arsort($candidates_votes, SORT_NUMERIC);
    $candidates = [];
    foreach ($candidates_votes as $cid => $votes) {
      $candidates[$cid] = $this->getElection()->getCandidate($cid);
      if (isset($previous_cid) && isset($previous_votes) && $votes == $previous_votes) {
        // This candidate has equal votes to the previous one, so neither can exist in $this->$unambiguous_order.
        unset($this->unambiguous_order[$previous_cid]);
      }
      else {
        $this->unambiguous_order[$cid] = $votes;
      }
      $previous_cid = $cid;
      $previous_votes = $votes;
    }
    return $candidates;
  }

  /**
   * Get candidates' surpluses in descending order of size. // ERS97 5.2.3
   *
   * @return array
   *   Array of surpluses (floats), keyed by candidate ID.
   */
  public function getSurpluses() {
    $surpluses = [];
    foreach ($this->getElection()->getCandidates() as $cid => $candidate) {
      if ($candidate->getSurplus() > 0) {
        $surpluses[$cid] = $candidate->getSurplus();
      }
    }
    arsort($surpluses, SORT_NUMERIC);
    return $surpluses;
  }

  /**
   * Overrides parent::calculateQuota().
   *
   * Calculate the minimum number of votes a candidate needs in order to be
   * elected. // ERS97 5.1.6 // ERS97 5.4.8 // ERS97 6.14
   *
   * According to ERS97 5.1.6, this should be done by "dividing the total
   * valid vote by one more than the number of places to be filled. Take the
   * division to two decimal places. If the result is exact that is the quota.
   * Otherwise ignore the remainder, and add 0.01".
   *
   * @return float
   */
  protected function calculateQuota() {
    $num = $this->getElection()->getNumValidBallots() / ($this->getElection()->getNumSeats() + 1);
    $quota = ceil($num * 100) / 100;
    $this->quota = $quota;
    return $quota;
  }

}
