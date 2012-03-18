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
 *   A candidate in an election.
 */
class DrooPHP_Candidate {

  const $STATE_ELECTED;
  const $STATE_EXCLUDED;
  const $STATE_HOPEFUL;

  public $state;

  public function __construct() {
    // Every candidate begins in the 'hopeful' state.
    $this->state = self::STATE_HOPEFUL;
  }

}
