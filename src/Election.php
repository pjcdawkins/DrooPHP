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
  public function addBallot(BallotInterface $ballot) {
    // Store ballots keyed by their rankings.
    $key = implode(' ', $ballot->getRanking());
    if (isset($this->ballots[$key])) {
      // If a ballot with the same ranking already exists in the election, just
      // increase its value rather than adding a new ballot. This ensures that
      // the ballots array remains as compressed as possible.
      $this->ballots[$key]->addValue($ballot->getValue());
    }
    else {
      $this->ballots[$key] = $ballot;
      // Ensure that ballots are sorted by their first preferences.
      ksort($this->ballots);
    }
    // Increase the count of valid ballots in the election.
    $this->num_valid_ballots += $ballot->getValue();
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
  }

  /**
   * @{inheritdoc}
   */
  public function getNumBallots() {
    return $this->getNumValidBallots() + $this->num_invalid_ballots;
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
  }

}
