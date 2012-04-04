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

  /** @var int */
  protected $_votes = 0;

  /** @var array */
  protected $_messages = array(); // @todo move messages into proper data

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
   * Give the candidate more votes.
   */
  public function addVotes($num) {
    $this->_votes += (int) $num;
  }

  /**
   * Get the candidate's number of votes.
   *
   * @return int
   */
  public function getVotes() {
    return $this->_votes;
  }

  /**
   * Log a message about the candidate.
   *
   * @param string $message
   *
   * @return void
   */
  public function log($message) {
    $this->_messages[] = $message;
  }

}
