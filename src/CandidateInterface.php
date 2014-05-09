<?php
/**
 * @file
 * Interface for a candidate in an election.
 */

namespace DrooPHP;

use DrooPHP\Exception\CandidateException;

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
   * Log a message.
   *
   * @param string $message
   */
  public function log($message);

  /**
   * Get the log of messages.
   *
   * @param bool $reset
   *
   * @return array
   */
  public function getLog($reset = FALSE);

  /**
   * Get the number of votes for the candidate.
   *
   * @return int|float
   */
  public function getVotes();

  /**
   * Add votes to the candidate.
   *
   * @param int|float $votes
   */
  public function addVotes($votes);

  /**
   * Transfer votes to another candidate.
   *
   * @param float|int $amount
   * @param CandidateInterface $to
   * @param int $precision
   *
   * @throws CandidateException
   */
  public function transferVotes($amount, CandidateInterface $to, $precision = 5);

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
   */
  public function setSurplus($amount, $increment = FALSE);

}
