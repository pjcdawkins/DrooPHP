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
   *
   * @param int|float $value
   *    The value of this ballot (default: 1).
   */
  public function __construct(array $ranking, $value = 1);

}
