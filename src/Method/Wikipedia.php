<?php
/**
 * @file
 * Method for counting votes described in the Wikipedia article "Single
 * transferable vote".
 */

namespace DrooPHP\Method;

use DrooPHP\CandidateInterface;

class Wikipedia extends MethodBase {

  /**
   * @{inheritdoc}
   */
  public function getName() {
    return 'Wikipedia example';
  }

  /**
   * Overrides parent::run().
   *
   * Run a count.
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
   *
   * @param int $stage The stage number.
   *
   * @throws \Exception
   * @return bool
   */
  public function run($stage = 1) {

    $election = $this->getElection();

    // First stage.
    if ($stage == 1) {
      $this->calculateQuota();
      // Count the first preference votes and add them to each candidate.
      foreach ($election->getBallots() as $ballot) {
        // The vote is an array of one or more candidate IDs (usually just one,
        // unless equal rankings are allowed).
        $first_preference = $ballot->getPreference(1);
        $num_equal = count($first_preference);
        foreach ($first_preference as $cid) {
          $candidate = $election->getCandidate($cid);
          // The worth of a vote is inversely proportional to the number of equal rankings in the vote, e.g. for B=C both B and C receive half a vote.
          $candidate->addVotes((1 / $num_equal) * $ballot->getValue());
        }
        $ballot->last_used_level = 1;
      }
      $this->logStage(0);
      // If there are any withdrawn candidates, transfer their votes.
      $withdrawn = $election->getCandidates(CandidateInterface::STATE_WITHDRAWN);
      foreach ($withdrawn as $candidate) {
        if ($candidate->getVotes()) {
          $this->logChange($candidate, sprintf('Withdrawn: all %d votes will be transferred.', $candidate->getVotes()), $stage);
          $this->transferVotes($candidate->getVotes(), $candidate, $stage);
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
        $election->num_filled_seats++;
        $anyone_elected = TRUE;
        $surplus = $candidate->getVotes() - $quota;
        $this->logChange($candidate, sprintf('Elected at stage %d, with a surplus of %s votes.', $stage, $surplus), $stage);
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
        $candidate->setState(CandidateInterface::STATE_DEFEATED);
        $this->logChange($candidate, sprintf('Defeated at stage %d, with %s votes.', $stage, $candidate->getVotes()), $stage);
        if ($candidate->getVotes() && !$this->isComplete()) {
          $this->transferVotes($candidate->getVotes(), $candidate, $stage);
          if ($this->getConfig()->getOption('allow_equal')) {
            $candidate->setVotes(round($candidate->getVotes(), 0)); // compensate for rounding errors in transfer with equal rankings
          }
        }
      }
    }

    $hopefuls = $election->getCandidates(CandidateInterface::STATE_HOPEFUL);
    // If there are as many seats as remaining candidates, all the remaining candidates are elected.
    $num_vacancies = $this->getNumVacancies();
    if (count($hopefuls) == $num_vacancies) {
      foreach ($hopefuls as $candidate) {
        $candidate->setState(CandidateInterface::STATE_ELECTED);
        $this->logChange($candidate, sprintf('Elected at stage %d, by default.', $stage), $stage);
        $election->num_filled_seats++;
      }
    }
    // If there are no remaining vacancies, all the remaining candidates are defeated.
    else {
      if ($num_vacancies == 0) {
        foreach ($hopefuls as $candidate) {
          $candidate->setState(CandidateInterface::STATE_DEFEATED);
          $this->logChange($candidate, sprintf('Defeated at stage %d, by default.', $stage), $stage);
        }
      }
    }

    $this->logStage($stage);

    // Proceed to the next stage or stop if the election is complete.
    if ($this->isComplete()) {
      return TRUE;
    }
    else if ($stage >= $this->getConfig()->getOption('max_stages')) {
      throw new \Exception(sprintf(
        'Maximum number of stages reached (%d) before completing the count.',
        $this->getConfig()->getOption('max_stages')
      ));
    }
    return $this->run($stage + 1);
  }

  /**
   * Get the hopeful candidate with the fewest votes.
   *
   * @return CandidateInterface
   */
  public function findDefeatableCandidate() {
    $hopefuls = $this->getElection()->getCandidates(CandidateInterface::STATE_HOPEFUL);
    // Candidates can only be defeated if sufficient candidates remain to fill all the vacancies.
    if (count($hopefuls) <= $this->getNumVacancies()) {
      return FALSE;
    }
    $defeatable = FALSE;
    foreach ($hopefuls as $candidate) {
      if (!($defeatable instanceof CandidateInterface && $candidate->getVotes() < $defeatable->getVotes())) {
        $defeatable = $candidate;
      }
    }
    return $defeatable;
  }

  /**
   * Transfer a candidate's votes or surplus to other hopefuls.
   *
   * @param float $num_to_transfer
   * @param CandidateInterface $from_candidate
   * @param int $stage
   */
  public function transferVotes($num_to_transfer, CandidateInterface $from_candidate, $stage) {
    $election = $this->getElection();
    $hopefuls = $election->getCandidates(CandidateInterface::STATE_HOPEFUL);
    $votes = [];
    foreach ($election->getBallots() as $ballot) {
      $ranking = $ballot->getRanking();
      $last_used_level = $ballot->last_used_level;
      if (!isset($ranking[$last_used_level]) || $ranking[$last_used_level] != $from_candidate->getId()) {
        // Not a relevant ballot.
        continue;
      }
      $to_cids = $ballot->getPreference($last_used_level + 1);
      if (!$to_cids) {
        // No preference given. This is an exhausted ballot.
        $election->num_exhausted_ballots++;
        continue;
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
        $value = (1 / $count_to_cids) * $ballot->getValue();
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
      $from_candidate->addVotes(- $amount);
      $to_candidate->addVotes($amount);
      $this->logChange($from_candidate, sprintf('Transferred %s votes to %s.', $amount, $to_candidate->getName(), $stage), $stage);
      $this->logChange($to_candidate, sprintf('Received %s votes from %s.', $amount, $from_candidate->getName(), $stage), $stage);
    }
  }

}
