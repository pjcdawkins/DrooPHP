<?php
/**
 * @file
 * Class representing a candidate in an election.
 */

namespace DrooPHP;

class Candidate implements CandidateInterface {

  const STATE_ELECTED = 2;
  const STATE_HOPEFUL = 1;
  const STATE_WITHDRAWN = 0;
  const STATE_DEFEATED = -1;

  public $name;

  public $cid;

  public $state;

  public $votes = 0;

  /**
   * @{inheritdoc}
   */
  public function __construct($name, $withdrawn = FALSE) {
    $this->name = $name;
    // Every candidate begins in either the 'hopeful' or 'withdrawn' state.
    $this->state = $withdrawn ? self::STATE_WITHDRAWN : self::STATE_HOPEFUL;
  }

  /**
   * Get the candidate's state as an English string.
   *
   * @return string
   */
  public function getFormattedState() {
    switch ($this->state) {
      case self::STATE_DEFEATED:
        return 'Defeated';
      case self::STATE_WITHDRAWN:
        return 'Withdrawn';
      case self::STATE_ELECTED:
        return 'Elected';
      case self::STATE_HOPEFUL:
        return 'Hopeful';
    }
  }

}
