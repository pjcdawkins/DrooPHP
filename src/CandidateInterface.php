<?php
/**
 * @file
 * Interface for a candidate in an election.
 */

namespace DrooPHP;

interface CandidateInterface {

  const STATE_ELECTED = 2;
  const STATE_HOPEFUL = 1;
  const STATE_WITHDRAWN = 0;
  const STATE_DEFEATED = -1;

  /**
   * Constructor.
   *
   * @param string $name
   *   The name of the candidate.
   * @param int $id
   *   The ID of the candidate.
   * @param bool $withdrawn
   *   Whether the candidate has been withdrawn.
   */
  public function __construct($name, $id, $withdrawn = FALSE);

  /**
   * Get the number of votes for the candidate.
   *
   * @return int|float
   */
  public function getVotes();

  /**
   * Set the number of votes for the candidate.
   *
   * @param int|float $votes
   *
   * @return self
   */
  public function setVotes($votes);

  /**
   * Add to the number of votes for the candidate.
   *
   * @param int|float $amount
   *
   * @return self
   */
  public function addVotes($amount);

  /**
   * Get the candidate's name.
   *
   * @return string
   */
  public function getName();

  /**
   * Get the candidate ID.
   *
   * @return int
   */
  public function getId();

  /**
   * Set the candidate state.
   *
   * @param int $state
   *   One of the CandidateInterface constants: STATE_ELECTED, STATE_HOPEFUL,
   *   STATE_WITHDRAWN, or STATE_DEFEATED.
   *
   * @return self
   */
  public function setState($state);

  /**
   * Get the candidate state.
   *
   * @param bool $formatted
   *   Whether to format the state as a string for output. Default: FALSE.
   *
   * @return int|string
   */
  public function getState($formatted = FALSE);

}
