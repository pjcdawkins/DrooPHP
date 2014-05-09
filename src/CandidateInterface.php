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
   */
  public function __construct($name, $id);

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
   * @param bool $increment
   *
   * @return self
   */
  public function setVotes($votes, $increment = FALSE);

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

  /**
   * Get the candidates' surplus votes.
   *
   * @return int|float
   */
  public function getSurplus();

  /**
   * Set the candidates' surplus votes.
   *
   * @param int|float $amount
   * @param bool $increment
   *
   * @return self
   */
  public function setSurplus($amount, $increment = FALSE);

}
