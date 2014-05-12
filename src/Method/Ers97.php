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
use DrooPHP\ElectionInterface;
use DrooPHP\Exception\CountException;

class Ers97 extends Stv {

  public $precision = 2;

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
   * @{inheritdoc}
   *
   * Overrides \DrooPHP\Method\Stv::run().
   */
  public function run($stage = 1) {

    $election = $this->getElection();

    // First stage. // ERS97 5.1
    if ($stage == 1) {
      // Count the first preference votes and add them to each candidate // ERS97 5.1.4
      foreach ($election->getBallots() as $ballot) {
        $worth = $ballot->getNextPreferenceWorth();
        foreach ($ballot->getPreference(1) as $candidate_id) {
          $election->getCandidate($candidate_id)->addVotes($worth);
        }
        $ballot->setLastUsedLevel(1);
      }
    }

    // Elect candidates // ERS97 5.1.7 // ERS97 5.3.13 // ERS97 5.4.9
    /*
        Considering each candidate in turn in descending order of their votes, deem
        elected any candidate whose vote equals or exceeds:
            (a) the quota, or
            (b) (on very rare occasions, where this is less than the quota), the total
                    active vote, divided by one more than the number of places not yet filled.
        up to the number of places to be filled, subject to paragraph 5.6.2. // ERS97 5.6.2 refers to ties
     */
    $candidates = $this->getCandidatesOrder(); // sort into descending order of votes
    $active_vote = $this->getActiveVote();
    $quota = $this->getQuota(TRUE);
    $anyone_elected = FALSE;
    foreach ($candidates as $candidate) {
      $num_vacancies = $this->getNumVacancies();
      if ($num_vacancies == 0) {
        // If all seats are filled, the election has finished.
        $this->logStage($stage);
        return TRUE;
      }
      if ($candidate->getState() !== CandidateInterface::STATE_HOPEFUL) {
        continue;
      }
      $votes = $candidate->getVotes();
      $threshold = $active_vote / ($num_vacancies + 1);
      if ($votes >= $quota || $votes >= $threshold) {
        // The candidate is now elected.
        $candidate->setState(CandidateInterface::STATE_ELECTED);
        $anyone_elected = TRUE;
        $surplus = $candidate->getVotes() - $quota;
        if ($surplus) {
          $candidate->setSurplus($surplus);
          $candidate->log(sprintf('Elected at stage %d with a surplus of %s votes', $stage, number_format($surplus, $this->precision)));
        }
        else {
          $candidate->log(sprintf('Elected at stage %d', $stage));
        }
      }
    }

    if ($stage == 1) {
      // If we're on the first stage, it's now complete, the next actions are part of stage 2. // ERS97 5.1.8
      $this->logStage($stage);
      return $this->run($stage + 1);
    }

    if ($anyone_elected) {
      // If anyone has been elected in this stage, then it's now complete.
      $this->logStage($stage);
      return $this->run($stage + 1);
    }

    // If one or more candidates have surpluses, the largest of these should now be transferred. // ERS97 5.2.2
    $surpluses = $this->getSurpluses();
    $surpluses_total = array_sum($surpluses);

    // Find "The difference between the votes of the two candidates who have the
    // fewest votes".
    $votes = array();
    $candidate_fewest_votes_diff = 0;
    foreach (array_slice($this->getCandidatesOrder(), -2) as $candidate) {
      $votes[] = $candidate->getVotes();
    }
    if (count($votes) == 2) {
      $candidate_fewest_votes_diff = $votes[0] - $votes[1];
    }

    // ERS 5.2.2 (a)
    if ($surpluses_total && $surpluses_total <= $candidate_fewest_votes_diff) {
      // Defer surpluses to the next stage.
      $this->logStage($stage);
      return $this->run($stage + 1);
    }
    // @todo ERS 5.2.2 (b)
    elseif ($surpluses_total) {
      // Transfer the largest surplus.
      foreach ($surpluses as $cid => $surplus) {
        $candidate = $election->getCandidate($cid);
        $this->transferVotes($surplus, $candidate);
        $candidate->setSurplus(-$surplus, TRUE);
        // The transfer of a surplus constitutes a stage in the count. // ERS97 5.2.4
        $this->logStage($stage);
        return $this->run($stage + 1);
        break;
      }
    }

    // Eliminate candidates.
    // ERS97 5.2.5: If, after all surpluses have been transferred or deferred,
    // one or more places remain to be filled, the candidate or candidates with the
    // fewest votes must be excluded. Exclude as many candidates together as
    // possible, provided that:
    //   (a) Sufficient candidates remain to fill all the remaining places
    //   (b) The total votes of these candidates, together with the total of any
    //       deferred surpluses, does not exceed the vote of the candidate next
    //       above.
    // If the votes of two or more candidates are equal, and those candidates
    // have the fewest votes, exclude the candidate who had the fewest votes at
    // the first stage or at the earliest point in the count, after the transfer
    // of a batch of papers, where they had unequal votes. If the votes of such
    // candidates have been equal at all such points the Returning Officer shall
    // decide which candidate to exclude by lot.
    $number_possible_to_exclude = count($election->getCandidates(CandidateInterface::STATE_HOPEFUL)) - $this->getNumVacancies();
    $excludable = [];
    $excludable_vote = 0;
    // Go through candidates in ascending order of votes.
    $candidates = $this->getCandidatesOrder();
    foreach (array_reverse($candidates) as $candidate) {
      if ($candidate->getState() !== $candidate::STATE_HOPEFUL) {
        continue;
      }
      $votes = $candidate->getVotes();
      $excludable_count = count($excludable);
      if (($number_possible_to_exclude <= $excludable_count)
         || ($excludable_count && $votes >= $excludable_vote)) {
        break;
      }
      $excludable_vote += $votes;
      $excludable[] = $candidate;
    }
    // Exclude the excludable candidates.
    foreach ($excludable as $candidate) {
      $candidate->setState(CandidateInterface::STATE_DEFEATED);
      $votes = $candidate->getVotes();
      $candidate->log(sprintf('Defeated at stage %d, with %s votes.', $stage, $votes ? number_format($votes, $this->precision) : 'no'));
      if ($votes) {
        $this->transferVotes($votes, $candidate);
      }
    }

    $this->logStage($stage);

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
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
   * @{inheritdoc}
   *
   * @todo make this ERS97 compliant
   */
  public function transferVotes($amount, CandidateInterface $from_candidate) {
    parent::transferVotes($amount, $from_candidate);
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
    $election = $this->getElection();
    $num = $election->getNumValidBallots() / ($election->getNumSeats() + 1);
    return ceil($num * 100) / 100;
  }

}
