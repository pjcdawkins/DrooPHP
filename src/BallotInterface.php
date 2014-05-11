<?php
/**
 * @file
 * Interface for a ballot.
 */

namespace DrooPHP;

interface BallotInterface {

  /**
   * Constructor.
   *
   * @param array $ranking
   *    The ranking, expressed as an array of candidate IDs keyed by their
   *    preference level (e.g. the second preference candidate is keyed by 2).
   *    Each array element could itself be an array of candidate IDs, if equal
   *    ranking is allowed.
   *
   * @param int|float $value
   *    The value of this ballot (default: 1).
   */
  public function __construct(array $ranking, $value = 1);

  /**
   * Get the candidate(s) ranked at the given preference level.
   *
   * @param int $level
   *
   * @return array
   */
  public function getPreference($level);

  /**
   * Get the candidate(s) ranked at the last used preference level.
   *
   * @see self::setLastUsedLevel()
   *
   * @return array
   */
  public function getLastPreference();

  /**
   * Get the candidate(s) ranked at the next preference level.
   *
   * @see self::setLastUsedLevel()
   *
   * @return array
   */
  public function getNextPreference();

  /**
   * Get the worth of each vote at the next preference level.
   *
   * The worth of the vote is 1 divided by the number of candidates who are
   * ranked equally at this level (usually 1), multiplied by the ballot's value
   * (also usually 1).
   *
   * @return float|int
   */
  public function getNextPreferenceWorth();

  /**
   * Get the ballot's value.
   *
   * @return int
   */
  public function getValue();

  /**
   * Add to the ballot's value.
   *
   * @param int $amount
   */
  public function addValue($amount);

  /**
   * Set the preference level last used during a count.
   *
   * @param int $level
   * @param bool $increment
   */
  public function setLastUsedLevel($level, $increment = FALSE);

}
