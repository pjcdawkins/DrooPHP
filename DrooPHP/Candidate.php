<?php
/**
 * @file
 *   DrooPHP_Candidate class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Candidate
 *   Container for a candidate in an election.
 */
class DrooPHP_Candidate {

  const STATE_ELECTED = 1;
  const STATE_EXCLUDED = -1;
  const STATE_HOPEFUL = 0;

  public $state;

  public function __construct() {
    // Every candidate begins in the 'hopeful' state.
    $this->state = self::STATE_HOPEFUL;
  }

}
