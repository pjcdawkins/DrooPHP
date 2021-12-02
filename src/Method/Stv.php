<?php
/**
 * @file
 * Method for counting votes described in the Wikipedia article "Single
 * transferable vote".
 */

namespace DrooPHP\Method;

use DrooPHP\CandidateInterface;
use DrooPHP\Exception\CountException;

class Stv extends MethodBase {

  protected $non_transferable_vote = 0;

  /**
   * @{inheritdoc}
   */
  public function getName() {
    return 'STV';
  }

  /**
   * @{inheritdoc}
   *
   * From Wikipedia:
   *     An STV election proceeds according to the following steps:
   *         1. Any candidate who has reached or exceeded the quota is
   *            declared elected.
   *         2. If a candidate has more votes than the quota, that candidate's
   *            surplus votes are transferred to other candidates. Votes that
   *            would have gone to the winner instead go to the next
   *            preference listed on their ballot.
   *         3. If no one new meets the quota, the candidate with the fewest
   *            votes is eliminated and that candidate's votes are
   *            transferred.
   *         4. This process repeats until either a winner is found for every
   *            seat or there are as many seats as remaining candidates.
   *
   * See:
   * http://en.wikipedia.org/wiki/Single_transferable_vote#Finding_the_winners
   */
  public function run($stage = 1) {

    $election = $this->getElection();

    // First stage.
    if ($stage == 1) {
      // Count the first preference votes and add them to each candidate.
      foreach ($election->getBallots() as $ballot) {
        $worth = $ballot->getNextPreferenceWorth();
        foreach ($ballot->getNextPreference() as $candidate_id) {
          $election->getCandidate($candidate_id)->addVotes($worth);
        }
        $ballot->setLastUsedLevel(1);
      }
      $this->logStage(0);
      // If there are any withdrawn candidates, transfer their votes.
      $withdrawn = $election->getCandidates(CandidateInterface::STATE_WITHDRAWN);
      foreach ($withdrawn as $candidate) {
        $votes = $candidate->getVotes();
        if ($votes) {
          $candidate->log(sprintf('Withdrawn: all %d votes will be transferred.', $votes));
          $this->transferVotes($votes, $candidate);
        }
      }
    }

    // Elect candidates
    $hopefuls = $election->getCandidates(CandidateInterface::STATE_HOPEFUL);
    $quota = $this->getQuota();
    $anyone_elected = FALSE;
    foreach ($hopefuls as $candidate) {
      // A candidate is elected if their votes equal or exceed the quota.
      if ($candidate->getVotes() >= $quota) {
        $candidate->setState(CandidateInterface::STATE_ELECTED);
        $surplus = $candidate->getVotes() - $quota;
        if ($surplus) {
          $candidate->setSurplus($surplus);
          $candidate->log(sprintf('Elected at stage %d, with a surplus of %s votes.', $stage, number_format($surplus)));
          $this->transferVotes($surplus, $candidate);
        }
        else {
          $candidate->log(sprintf('Elected at stage %d.', $stage));
        }
        $anyone_elected = TRUE;
      }
    }

    // Eliminate candidates.
    // "If no one new meets the quota, the candidate with the fewest votes is eliminated and that candidate's votes are transferred."
    $candidate = $this->findDefeatableCandidate();
    if (!$anyone_elected && $candidate) {
      $candidate->setState(CandidateInterface::STATE_DEFEATED);
      $votes = $candidate->getVotes();
      $candidate->log(sprintf('Defeated at stage %d, with %s votes.', $stage, $votes ? number_format($votes) : 'no'));
      if ($votes) {
        $this->transferVotes($votes, $candidate);
      }
    }

    $hopefuls = $election->getCandidates(CandidateInterface::STATE_HOPEFUL);
    // If there are as many seats as remaining candidates, all the remaining candidates are elected.
    $num_vacancies = $this->getNumVacancies();
    if (count($hopefuls) == $num_vacancies) {
      foreach ($hopefuls as $candidate) {
        $candidate->setState(CandidateInterface::STATE_ELECTED);
        $candidate->log(sprintf('Elected at stage %d, by default.', $stage));
      }
    }
    // If there are no remaining vacancies, all the remaining candidates are defeated.
    else {
      if ($num_vacancies == 0) {
        foreach ($hopefuls as $candidate) {
          $candidate->setState(CandidateInterface::STATE_DEFEATED);
          $candidate->log(sprintf('Defeated at stage %d, by default.', $stage));
        }
      }
    }

    $this->logStage($stage);

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
    }
    elseif ($stage >= $this->getConfig()->getOption('max_stages')) {
      throw new CountException(sprintf(
        'Maximum number of stages reached (%d) before completing the count.',
        $this->getConfig()->getOption('max_stages')
      ));
    }
    return $this->run($stage + 1);
  }

  /**
   * Get the hopeful candidate with the fewest votes.
   *
   * @return CandidateInterface|false
   */
  public function findDefeatableCandidate() {
    $hopefuls = $this->getElection()
      ->getCandidates(CandidateInterface::STATE_HOPEFUL);
    // Candidates can only be defeated if sufficient candidates remain to fill all the vacancies.
    if (count($hopefuls) <= $this->getNumVacancies()) {
      return FALSE;
    }
    $defeatable = FALSE;
    foreach ($hopefuls as $candidate) {
      if (!$defeatable instanceof CandidateInterface || $candidate->getVotes() < $defeatable->getVotes()) {
        $defeatable = $candidate;
      }
    }
    return $defeatable;
  }

  /**
   * Overrides parent::logStage().
   */
  public function logStage($stage) {
    parent::logStage($stage);
    $this->stages[$stage]['non_transferable'] = $this->non_transferable_vote;
  }

  /**
   * Transfer a candidate's votes or surplus to other hopefuls.
   *
   * @param float $num_to_transfer
   * @param CandidateInterface $from_candidate
   *
   * @todo allow transferring to [other] winning candidates, as in Meek/Warren
   */
  public function transferVotes($num_to_transfer, CandidateInterface $from_candidate) {
    $election = $this->getElection();
    $hopefuls = $election->getCandidates(CandidateInterface::STATE_HOPEFUL);
    // Go through the election's ballots. For each one, find the next preference
    // candidate(s), if $from_candidate was the last preference candidate. Add
    // together the value (worth) of all of these ballots, and find what this is
    // as a proportion of $num_to_transfer.
    $votes = [];
    foreach ($election->getBallots() as $ballot) {
      if (!in_array($from_candidate->getId(), $ballot->getLastPreference())) {
        // Not a relevant ballot.
        continue;
      }
      $worth = $ballot->getNextPreferenceWorth();
      foreach ($ballot->getNextPreference() as $candidate_id) {
        if (!isset($hopefuls[$candidate_id])) {
          // The next preference candidate is not in the running.
          continue;
        }
        if (!isset($votes[$candidate_id])) {
          $votes[$candidate_id] = 0;
        }
        $votes[$candidate_id] += $worth;
      }
      // Increment the last used preference level of the ballot.
      $ballot->setLastUsedLevel(1, TRUE);
    }
    // To convert this into a ratio, find the total number of votes.
    $total_votes = array_sum($votes);
    // Run the transfer.
    $transferred = 0;
    foreach ($votes as $to_cid => $num_votes) {
      $amount = ($num_to_transfer / $total_votes) * $num_votes;
      if (!$amount) {
        continue;
      }
      $to_candidate = $hopefuls[$to_cid];
      $from_candidate->transferVotes($amount, $to_candidate);
      $transferred += $amount;
    }
    $this->non_transferable_vote += $num_to_transfer - $transferred;
  }

}
