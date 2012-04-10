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
   * The number of seats to be filled.
   * @var int
   */
  public $num_seats = 0;

  /**
   * The number of seats filled.
   * @var int
   */
  public $num_filled_seats = 0;

  /**
   * The total number of (invalid and valid) ballots.
   * @var int
   */
  public $num_ballots = 0;

  /**
   * The total number of valid ballots.
   * @var int
   */
  public $num_valid_ballots = 0;

  /**
   * The total number of invalid ballots.
   * @var int
   */
  public $num_invalid_ballots = 0;

  /**
   * The ballots: array of DrooPHP_Ballot objects.
   *
   * @var array
   */
  public $ballots = array();

  /**
   * The total number of candidates standing.
   * @var int
   */
  public $num_candidates = 0;

  /**
   * The candidates: array of DrooPHP_Candidate objects keyed by candidate ID.
   *
   * @var array
   */
  public $candidates = array();


  /**
   * The set of withdrawn candidate IDs.
   * @var array
   */
  public $withdrawn = array();

  /** @var int */
  protected $_cid_increment = 1;

  /**
   * Get a single candidate by ID
   *
   * @param mixed $cid
   * @return DrooPHP_Candidate
   */
  public function getCandidate($cid) {
    if (!isset($this->candidates[$cid])) {
      throw new DrooPHP_Exception(sprintf('The candidate "%s" does not exist.', $cid));
    }
    return $this->candidates[$cid];
  }

  /**
   * Add a candidate.
   *
   * @param string $name
   */
  public function addCandidate($name) {
    $cid = $this->_cid_increment;
    if ($cid > $this->num_candidates) {
      throw new DrooPHP_Exception('Attempted to add too many candidate names.');
    }
    $withdrawn = in_array($cid, $this->withdrawn);
    $this->candidates[$cid] = new DrooPHP_Candidate($name, $withdrawn);
    $this->_cid_increment++;
  }

}
