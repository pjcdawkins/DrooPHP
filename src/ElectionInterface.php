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
   * @return Candidate
   */
  public function getCandidate($cid);

  /**
   * Get an array of candidates who have the given state.
   *
   * @param $state
   *
   * @return array
   * Array of Candidate objects keyed by candidate ID.
   */
  public function getCandidatesByState($state);

  /**
   * Add a candidate.
   *
   * @param string $name The name of the candidate.
   */
  public function addCandidate($name);

}
