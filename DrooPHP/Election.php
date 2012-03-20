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
 *   Container for an election profile, and the main parser for BLT file data.
 *
 *   The public interface of ElectionProfile:
 *     $title: title string from the ballot file
 *     $source: source string from blt file
 *     $comment: comment string from blt file
 *     $nSeats: the number of seats to be filled
 *     $nBallots: the number of ballots (possibly greater than len(rankings) because of
 *               ballot multipliers)
 *     $eligible: the set of non-withdrawn candidate IDs
 *     $withdrawn: the set of withdrawn candidate IDs
 *         eligible and withdrawn should be treated as frozenset (unordered and immutable)
 *         though they may be implemented as any iterable.
 *     $ballotLines: a list of BallotLine objects with not equal rankings, each with a:
 *        multiplier: a repetition count >=1
 *        ranking: an array of candidate IDs
 *     $ballotLinesequal: a list of BallotLine objects with at least one equal ranking, each with a:
 *        multiplier: a repetition count >=1
 *        ranking: tuple of tuples of candidate IDs
 *     $tieOrder[cid]: tiebreaking order, by CID
 *     $nickName[cid]: short name of candidate, by CID
 *     $options: list of election options from ballot file
 *     $candidateName[cid]  full name of candidate, by CID
 *     $candidateOrder[cid] ballot order of candidate, by CID
 *   All attributes should be treated as immutable.
 */
class DrooPHP_Election {

  /** @var string */
  public $title;
  /** @var string */
  public $source;
  /** @var string */
  public $comment;

  /**
   * Array of DrooPHP_Candidate objects.
   *
   * @var array
   */
  protected $_candidates = array();

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
   * Mark candidate IDs as withdrawn.
   *
   * @todo validate this after the candidates are added.
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
   * @param mixed $id
   * @return DrooPHP_Candidate
   */
  public function getCandidate($id) {
    if (!isset($this->_candidates[$id])) {
      throw new DrooPHP_Exception(sprintf('The candidate "%s" does not exist.', $id));
    }
    return $this->_candidates[$id];
  }

  /**
   * Add a candidate.
   *
   * @param int $id
   * @param string $name
   */
  public function addCandidate($name) {
    $id = $this->_cid_increment;
    if ($id > $this->_num_candidates) {
      throw new DrooPHP_Exception('Attempted to add too many candidate names.');
    }
    $withdrawn = (bool) in_array($id, $this->_withdrawn);
    $this->_candidates[$id] = new DrooPHP_Candidate($name, $withdrawn);
    $this->_cid_increment++;
  }

}
