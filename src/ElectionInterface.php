<?php
/**
 * @file
 * Interface for an election.
 */

namespace DrooPHP;

interface ElectionInterface {

  /**
   * Get a single candidate by ID.
   *
   * @param mixed $cid
   *
   * @return CandidateInterface
   */
  public function getCandidate($cid);

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
   * @param string $name The name of the candidate.
   */
  public function addCandidate($name);

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
   * @param string $key
   */
  public function addBallot(BallotInterface $ballot, $key);

}
