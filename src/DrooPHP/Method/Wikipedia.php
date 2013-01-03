<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

use \DrooPHP\Method;
use \DrooPHP\Candidate;

/**
 * Method for counting votes described in the Wikipedia article "Single
 * transferable vote".
 */
class Wikipedia extends Method
{

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
     * @return bool
     */
    public function run($stage = 1)
    {

        $election = $this->election;

        // First stage.
        if ($stage == 1) {
            $this->calculateQuota();
            // Count the first preference votes and add them to each candidate.
            foreach ($election->ballots as $ballot) {
                // The vote is an array of one or more candidate IDs (usually just one, unless equal rankings are allowed).
                $first_preference = (array) $ballot->ranking[1];
                $num_equal = count($first_preference);
                foreach ($first_preference as $cid) {
                    $candidate = $election->getCandidate($cid);
                    // The worth of a vote is inversly proportional to the number of equal rankings in the vote, e.g. for B=C both B and C receive half a vote.
                    $candidate->votes += (1 / $num_equal) * $ballot->value;
                }
                $ballot->last_used_level = 1;
            }
            $this->logStage(0);
            // If there are any withdrawn candidates, transfer their votes.
            $withdrawn = $election->getCandidatesByState(Candidate::STATE_WITHDRAWN);
            foreach ($withdrawn as $candidate) {
                if ($candidate->votes) {
                    $this->logChange($candidate, sprintf('Withdrawn: all %d votes will be transferred.', $candidate->votes), $stage);
                    $this->transferVotes($candidate->votes, $candidate, $stage);
                }
            }
        }

        // Elect candidates
        $hopefuls = $election->getCandidatesByState(Candidate::STATE_HOPEFUL);
        $quota = $this->quota;
        $anyone_elected = FALSE;
        foreach ($hopefuls as $candidate) {
            // A candidate is elected if their votes equal or exceed the quota.
            if ($candidate->votes >= $quota) {
                $candidate->state = Candidate::STATE_ELECTED;
                $election->num_filled_seats++;
                $anyone_elected = TRUE;
                $surplus = $candidate->votes - $quota;
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
                $candidate->state = Candidate::STATE_DEFEATED;
                $this->logChange($candidate, sprintf('Defeated at stage %d, with %s votes.', $stage, $candidate->votes), $stage);
                if ($candidate->votes && !$this->isComplete()) {
                    $this->transferVotes($candidate->votes, $candidate, $stage);
                    if ($this->config->getOption('allow_equal')) {
                        $candidate->votes = round($candidate->votes, 0); // compensate for rounding errors in transfer with equal rankings
                    }
                }
            }
        }

        $hopefuls = $election->getCandidatesByState(Candidate::STATE_HOPEFUL);
        // If there are as many seats as remaining candidates, all the remaining candidates are elected.
        $num_vacancies = $this->getNumVacancies();
        if (count($hopefuls) == $num_vacancies) {
            foreach ($hopefuls as $candidate) {
                $candidate->state = Candidate::STATE_ELECTED;
                $this->logChange($candidate, sprintf('Elected at stage %d, by default.', $stage), $stage);
                $election->num_filled_seats++;
            }
        }
        // If there are no remaining vacancies, all the remaining candidates are defeated.
        else if ($num_vacancies == 0) {
            foreach ($hopefuls as $candidate) {
                $candidate->state = Candidate::STATE_DEFEATED;
                $this->logChange($candidate, sprintf('Defeated at stage %d, by default.', $stage), $stage);
            }
        }

        $this->logStage($stage);

        // Proceed to the next stage or stop if the election is complete.
        if ($this->isComplete()) {
            return TRUE;
        }
        else if ($stage >= $this->config->getOption('max_stages')) {
            throw new \Exception(sprintf(
                'Maximum number of stages reached (%d) before completing the count.',
                $this->config->getOption('max_stages')
            ));
            return FALSE;
        }
        else {
            return $this->run($stage + 1);
        }
    }

    /**
     * Get the hopeful candidate with the fewest votes.
     *
     * @return Candidate
     */
    public function findDefeatableCandidate()
    {
        $election = $this->election;
        $hopefuls = $election->getCandidatesByState(Candidate::STATE_HOPEFUL);
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
     * @param Candidate $from_candidate
     * @param int $stage
     */
    public function transferVotes($num_to_transfer, Candidate $from_candidate, $stage)
    {
        $election = $this->election;
        $hopefuls = $election->getCandidatesByState(Candidate::STATE_HOPEFUL);
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
            $this->logChange($from_candidate, sprintf('Transferred %s votes to %s.', $amount, $to_candidate->name, $stage), $stage);
            $this->logChange($to_candidate, sprintf('Received %s votes from %s.', $amount, $from_candidate->name, $stage), $stage);
        }
    }

}
