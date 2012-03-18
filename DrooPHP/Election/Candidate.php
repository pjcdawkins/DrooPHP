<?php
/**
 * @file
 *   DrooPHP_Election_Candidate class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Election_Candidate
 *   Container for a candidate in an election.
 */
class DrooPHP_Election_Candidate {

  const STATE_ELECTED = 1;
  const STATE_EXCLUDED = -1;
  const STATE_HOPEFUL = 0;

  public $name;
  public $state;

  /**
   * Constructor.
   *
   * @param $name.
   *   The name of the candidate.
   */
  public function __construct($name) {
    // Every candidate begins in the 'hopeful' state.
    $this->state = self::STATE_HOPEFUL;

    $this->name = $name;
  }

}
