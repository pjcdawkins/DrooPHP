<?php
/**
 * @file
 * Interface for a candidate in an election.
 */

namespace DrooPHP;

interface CandidateInterface {

  /**
   * Constructor.
   *
   * @param string $name The name of the candidate.
   * @param bool $withdrawn Whether the candidate has been withdrawn.
   */
  public function __construct($name, $withdrawn = FALSE);

}
