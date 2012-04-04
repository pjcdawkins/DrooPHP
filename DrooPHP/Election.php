<?php
/**
 * @file
 *   DrooPHP_Election class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Election
 *   Contains details about an election.
 */
class DrooPHP_Election {

  /** @var string */
  public $title;
  /** @var string */
  public $source;
  /** @var string */
  public $comment;

  /**
   * An array of elected candidates.
   */
  public $elected = array();

  /**
   * The number of seats (vacancies) to be filled.
   * @var int
   */
  protected $_num_seats;

  /**
   * The total number of candidates standing.
   * @var int
   */
  protected $_num_candidates;

  /**
   * The total number of ballots.
   * @var int
   */
  protected $_num_ballots;

  /**
   * The candidates: array of DrooPHP_Candidate objects keyed by candidate ID.
   *
   * @var array
   */
  protected $_candidates = array();

  /**
   * The set of withdrawn candidate IDs.
   * @var array
   */
  protected $_withdrawn = array();

  /** @var int */
  protected $_cid_increment = 1;

  /**
   * Set the number of candidates.
   */
  public function setNumCandidates($int) {
    if (!is_numeric($int)) {
      throw new DrooPHP_Exception('The number of candidates must be an integer.');
    }
    $this->_num_candidates = (int) $int;
  }

  /**
   * Set the number of seats.
   */
  public function setNumSeats($int) {
    if (!is_numeric($int)) {
      throw new DrooPHP_Exception('The number of seats must be an integer.');
    }
    $this->_num_seats = (int) $int;
  }

  /**
   * Set the number of ballots.
   */
  public function setNumBallots($int) {
    if (!is_numeric($int)) {
      throw new DrooPHP_Exception('The number of ballots must be an integer.');
    }
    $this->_num_ballots = (int) $int;
  }

  /**
   * Mark candidate IDs as withdrawn.
   */
  public function setWithdrawn(array $ids) {
    $this->_withdrawn = $ids;
  }

  /**
   * Get the number of candidates.
   *
   * @return int
   */
  public function getNumCandidates() {
    return $this->_num_candidates;
  }

  /**
   * Get the number of seats.
   *
   * @return int
   */
  public function getNumSeats() {
    return $this->_num_seats;
  }

  /**
   * Get the number of ballots.
   *
   * @return int
   */
  public function getNumBallots() {
    return $this->_num_ballots;
  }

  /**
   * Get the candidates array.
   *
   * @return array
   */
  public function getCandidates() {
    return $this->_candidates;
  }

  /**
   * Get a single candidate by ID
   *
   * @param mixed $cid
   * @return DrooPHP_Candidate
   */
  public function getCandidate($cid) {
    if (!isset($this->_candidates[$cid])) {
      throw new DrooPHP_Exception(sprintf('The candidate "%s" does not exist.', $cid));
    }
    return $this->_candidates[$cid];
  }

  /**
   * Add a candidate.
   *
   * @param string $name
   */
  public function addCandidate($name) {
    $cid = $this->_cid_increment;
    if ($cid > $this->_num_candidates) {
      throw new DrooPHP_Exception('Attempted to add too many candidate names.');
    }
    $withdrawn = (bool) in_array($cid, $this->_withdrawn);
    $this->_candidates[$cid] = new DrooPHP_Candidate($name, $withdrawn);
    $this->_cid_increment++;
  }

}
