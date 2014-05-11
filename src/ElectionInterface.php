<?php
/**
 * @file
 * Interface for an election.
 */

namespace DrooPHP;

use DrooPHP\Exception\UsageException;

interface ElectionInterface {

  /**
   * Get a single candidate by ID.
   *
   * @param mixed $id
   *
   * @return CandidateInterface
   */
  public function getCandidate($id);

  /**
   * Get an array of candidates, optionally filtered by state
   *
   * @param $state
   *   The candidate state (see CandidateInterface constants).
   *
   * @return CandidateInterface[]
   *   An array of CandidateInterface objects keyed by candidate ID.
   */
  public function getCandidates($state = NULL);

  /**
   * Add a candidate.
   *
   * @param CandidateInterface $candidate
   *
   * @throws UsageException
   */
  public function addCandidate(CandidateInterface $candidate);

  /**
   * Get the ballots.
   *
   * @return BallotInterface[]
   */
  public function getBallots();

  /**
   * Get a ballot by its key.
   *
   * @param string $key
   *
   * @return BallotInterface|FALSE
   */
  public function getBallot($key);

  /**
   * Add a ballot.
   *
   * @param BallotInterface $ballot
   *   The ballot object.
   * @param string $key
   *   A unique key identifying the ballot's rankings. This is so that duplicate
   *   ballots can simply be given a higher value (which takes up less memory),
   *   and so that ballots can be kept in an order (sorted by the key).
   */
  public function addBallot(BallotInterface $ballot, $key = NULL);

  /**
   * Get the election title.
   *
   * @return string
   */
  public function getTitle();

  /**
   * Set the election title.
   *
   * @param string $title
   */
  public function setTitle($title);

  /**
   * Get the number of seats.
   *
   * @return int
   */
  public function getNumSeats();

  /**
   * Set the number of seats (vacancies).
   *
   * @param int $num
   */
  public function setNumSeats($num);

  /**
   * Set the total number of candidates.
   *
   * @param int $num
   */
  public function setNumCandidates($num);

  /**
   * Get the total number of candidates.
   *
   * This might not be the same as count($this->getCandidates()) because the
   * total count of candidates can be defined before all the candidates are
   * added.
   *
   * @return int
   */
  public function getNumCandidates();

  /**
   * Get the total number of ballots.
   *
   * @return int
   */
  public function getNumBallots();

  /**
   * Get the number of valid ballots.
   *
   * @return int
   */
  public function getNumValidBallots();

  /**
   * Add to the number of invalid ballots.
   *
   * @param int $amount
   */
  public function addNumInvalidBallots($amount);

  /**
   * Get the number of invalid ballots.
   *
   * @return int
   */
  public function getNumInvalidBallots();

}
