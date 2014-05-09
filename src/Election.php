<?php
/**
 * @file
 * Class representing an election.
 */

namespace DrooPHP;

use DrooPHP\Exception\UsageException;

class Election implements ElectionInterface {

  /** @var string */
  public $source;

  /** @var string */
  public $comment;

  /**
   * @var BallotInterface[]
   */
  protected $ballots = [];

  /**
   * @var CandidateInterface[]
   */
  protected $candidates = [];

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
  public function getCandidate($id) {
    if (!isset($this->candidates[$id])) {
      return FALSE;
    }
    return $this->candidates[$id];
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
   * @throws UsageException
   */
  public function addCandidate(CandidateInterface $candidate) {
    if (count($this->candidates) >= $this->num_candidates) {
      throw new UsageException('Attempted to add too many candidates.');
    }
    $id = $candidate->getId();
    if (isset($this->candidates[$id])) {
      throw new UsageException('A candidate already exists with the same ID.');
    }
    $this->candidates[$id] = $candidate;
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
  public function addBallot(BallotInterface $ballot, $key = NULL) {
    if ($key !== NULL) {
      $this->ballots[$key] = $ballot;
    }
    else {
      $this->ballots[] = $ballot;
    }
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function sortBallots() {
    ksort($this->ballots);
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
