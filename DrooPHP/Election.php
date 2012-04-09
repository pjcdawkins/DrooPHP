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
  public $num_seats;

  /**
   * The total number of candidates standing.
   * @var int
   */
  public $num_candidates;

  /**
   * The total number of ballots.
   * @var int
   */
  public $num_ballots;

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
