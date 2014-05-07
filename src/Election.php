<?php
/**
 * @file
 * Class representing an election.
 */

namespace DrooPHP;

class Election implements ElectionInterface {

  /** @var string */
  public $title;

  /** @var string */
  public $source;

  /** @var string */
  public $comment;

  /**
   * The number of seats to be filled.
   *
   * @var int
   */
  public $num_seats = 0;

  /**
   * The number of seats filled.
   *
   * @var int
   */
  public $num_filled_seats = 0;

  /**
   * The total number of (invalid and valid) ballots.
   *
   * @var int
   */
  public $num_ballots = 0;

  /**
   * The total number of valid ballots.
   *
   * @var int
   */
  public $num_valid_ballots = 0;

  /**
   * The total number of invalid ballots.
   *
   * @var int
   */
  public $num_invalid_ballots = 0;

  /**
   * The total number of exhausted ballots. These are ballots from which votes
   * could not be transferred because no further preferences were stated.
   *
   * @var int
   */
  public $num_exhausted_ballots = 0;

  /**
   * The ballots: array of BallotInterface objects.
   *
   * @var array
   */
  public $ballots = [];

  /**
   * The total number of candidates standing.
   *
   * @var int
   */
  public $num_candidates = 0;

  /**
   * The candidates: array of Candidate objects keyed by candidate ID.
   *
   * @var array
   */
  public $candidates = [];


  /**
   * The set of withdrawn candidate IDs.
   * @var array
   */
  public $withdrawn = [];

  /** @var int */
  protected $cid_increment = 1;

  /**
   * Get a single candidate by ID.
   *
   * @param mixed $cid
   *
   * @throws \Exception
   *
   * @return Candidate
   */
  public function getCandidate($cid) {
    if (!isset($this->candidates[$cid])) {
      throw new \Exception(sprintf('The candidate "%s" does not exist.', $cid));
    }
    return $this->candidates[$cid];
  }

  /**
   * Get an array of candidates who have the given state.
   *
   * @param $state
   *
   * @return array
   * Array of Candidate objects keyed by candidate ID.
   */
  public function getCandidatesByState($state) {
    $candidates = [];
    foreach ($this->candidates as $cid => $candidate) {
      if ($candidate->state === $state) {
        $candidates[$cid] = $candidate;
      }
    }
    return $candidates;
  }

  /**
   * Add a candidate.
   *
   * @param string $name The name of the candidate.
   *
   * @throws \Exception
   */
  public function addCandidate($name) {
    $cid = $this->cid_increment;
    if ($cid > $this->num_candidates) {
      throw new \Exception('Attempted to add too many candidate names.');
    }
    $withdrawn = in_array($cid, $this->withdrawn);
    $candidate = new Candidate($name, $withdrawn);
    $candidate->cid = $cid;
    $this->candidates[$cid] = $candidate;
    $this->cid_increment++;
  }

}
