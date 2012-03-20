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

  const STATE_ELECTED = 'elected';
  const STATE_HOPEFUL = 'hopeful';
  const STATE_WITHDRAWN = 'withdrawn';
  const STATE_DEFEATED = 'defeated';

  /** @var string */
  public $name;

  /** @var mixed */
  public $state;

  /** @var array */
  protected $_votes = array();

  /**
   * Constructor.
   *
   * @param string $name.
   *   The name of the candidate.
   * @param bool $withdrawn
   *   Whether the candidate has been withdrawn.
   */
  public function __construct($name, $withdrawn = FALSE) {
    $this->name = $name;
    // Every candidate begins in either the 'hopeful' or 'withdrawn' state.
    $this->state = $withdrawn? self::STATE_WITHDRAWN : self::STATE_HOPEFUL;
  }

  /**
   * Give the candidate more votes for a round.
   */
  public function addVotes($round, $num) {
    if (!isset($this->_votes[$round])) {
      $this->_votes[$round] = 0;
    }
    $this->_votes[$round] += (int) $num;
  }

  /**
   * Get the candidate's number of votes for a round.
   *
   * @return int
   */
  public function getVotes($round) {
    if (!isset($this->_votes[$round])) {
      throw new DrooPHP_Exception(
        'Attempted to get the votes of a candidate for a non-existent round.'
      );
    }
    return $this->_votes[$round];
  }

}
