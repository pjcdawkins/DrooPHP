<?php
/**
 * @file
 * Class representing an election.
 */

namespace DrooPHP;

class Election implements ElectionInterface {

  /** @var string */
  public $source;

  /** @var string */
  public $comment;

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
   * @var BallotInterface[]
   */
  public $ballots = [];

  /**
   * The set of withdrawn candidate IDs.
   * @var array
   */
  public $withdrawn = [];

  /**
   * @var CandidateInterface[]
   */
  protected $candidates = [];

  /**
   * @var int
   */
  protected $cid_increment = 1;

  /**
   * @var int
   */
  protected $num_candidates = 0;

  /**
   * @var int
   */
  protected $num_seats = 0;

  /**
   * @var int
   */
  protected $num_valid_ballots = 0;

  /**
   * @var int
   */
  protected $num_invalid_ballots = 0;

  /**
   * @var string
   */
  protected $title;

  /**
   * @{inheritdoc}
   */
  public function getCandidate($cid) {
    if (!isset($this->candidates[$cid])) {
      return FALSE;
    }
    return $this->candidates[$cid];
  }

  /**
   * @{inheritdoc}
   */
  public function getCandidates($state = NULL) {
    if ($state === NULL) {
      return $this->candidates;
    }
    $candidates = [];
    foreach ($this->candidates as $cid => $candidate) {
      if ($candidate->getState() === $state) {
        $candidates[$cid] = $candidate;
      }
    }
    return $candidates;
  }

  /**
   * @{inheritdoc}
   *
   * @throws \Exception
   */
  public function addCandidate($name) {
    $cid = $this->cid_increment;
    if ($cid > $this->num_candidates) {
      throw new \Exception('Attempted to add too many candidate names.');
    }
    $withdrawn = in_array($cid, $this->withdrawn);
    $candidate = new Candidate($name, $cid, $withdrawn);
    $this->candidates[$cid] = $candidate;
    $this->cid_increment++;
  }

  /**
   * @{inheritdoc}
   */
  public function getBallots() {
    return $this->ballots;
  }

  /**
   * @{inheritdoc}
   */
  public function getBallot($key) {
    if (!isset($this->ballots[$key])) {
      return FALSE;
    }
    return $this->ballots[$key];
  }

  /**
   * @{inheritdoc}
   */
  public function addBallot(BallotInterface $ballot, $key) {
    $this->ballots[$key] = $ballot;
  }

  /**
   * @{inheritdoc}
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @{inheritdoc}
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getNumSeats() {
    return $this->num_seats;
  }

  /**
   * @{inheritdoc}
   */
  public function setNumSeats($num) {
    $this->num_seats = $num;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getNumBallots() {
    return $this->num_valid_ballots + $this->num_invalid_ballots;
  }

  /**
   * @{inheritdoc}
   */
  public function addNumValidBallots($amount) {
    $this->num_valid_ballots += $amount;
    return $this;
  }
  /**
   * @{inheritdoc}
   */
  public function getNumValidBallots() {
    return $this->num_valid_ballots;
  }

  /**
   * @{inheritdoc}
   */
  public function addNumInvalidBallots($amount) {
    $this->num_invalid_ballots += $amount;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getNumInvalidBallots() {
    return $this->num_invalid_ballots;
  }

  /**
   * @{inheritdoc}
   */
  public function getNumCandidates() {
    return $this->num_candidates;
  }

  /**
   * @{inheritdoc}
   */
  public function setNumCandidates($num) {
    $this->num_candidates = $num;
    return $this;
  }

}
