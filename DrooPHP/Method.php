<?php
/**
 * @file
 *   DrooPHP_Method base class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Method
 *   Base class for a vote counting algorithm.
 */
class DrooPHP_Method {

  /** @var DrooPHP_Election */
  public $election;

  /** @var float */
  public $quota;

  public function __construct(DrooPHP_Election $election) {
    $this->election = $election;
  }

  /** @todo */
  public function countStage($round = 1) {
    $election = $this->election;
    foreach ($election->getCandidates() as $candidate) {
      switch ($candidate->state) {
        case DrooPHP_Candidate::STATE_DEFEATED:
        case DrooPHP_Candidate::STATE_WITHDRAWN:
          // do nothing
          break;
        case DrooPHP_Candidate::STATE_ELECTED:
          //  distribute votes
          break;
        case DrooPHP_Candidate::STATE_HOPEFUL:
          // add to hopeful candidates for this round
          break;
      }
    }
  }

  /** @todo */
  public function calculateQuota() {
  }

  /** @todo */
  public function distributeVotes() {
  }

  /** @todo */
  public function beatsQuota($num) {
    $quota = $this->quota;
    return $num > $quota? $num - $quota : FALSE;
  }

}
