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

  /** @var DrooPHP_Result */
  public $result;

  public function __construct(DrooPHP_Election $election) {
    $this->election = $election;
  }

  /** @todo */
  public function countStage($stage = 1) {
    $candidates = $this->election->candidates;
  }

  /** @todo */
  public function calculateQuota() {
  }

  /** @todo */
  public function distributeVotes() {
  }

  /** @todo */
  public function hasQuota(DrooPHP_Candidate $candidate) {
  }

}
