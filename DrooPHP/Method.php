<?php
/**
 * @file
 *   DrooPHP_Method base class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Method
 *   Base class for a vote counting algorithm.
 */
abstract class DrooPHP_Method {

  /** @var DrooPHP_Count */
  public $count;

  /** @var DrooPHP_Election */
  public $election;

  /** @var int */
  public $quota;

  /** @var int */
  public $num_elected;

  /**
   * Constructor
   *
   * @param DrooPHP_Count $count
   *   The object representing this count.
   */
  public function __construct(DrooPHP_Count $count) {
    $this->count = $count;
    $this->election = $count->election;
    $this->_calculateQuota();
  }

  /**
   * Run the election: this is the main, iterative method.
   */
  abstract public function run();

  /**
   * Transfer the votes from a successful or defeated candidate to the other
   * hopeful ones.
   *
   * @param mixed $from_cid
   * @param int $surplus
   * @param int $round
   */
  abstract public function transferVotes($from_cid, $surplus, $round);

  /**
   * Test whether the election is complete.
   *
   * @return bool
   */
  public function isComplete() {
    $election = $this->election;
    $num_seats = $election->getNumSeats();
    $num_candidates = $election->getNumCandidates();
    $must_be_elected = $num_seats;
    if ($num_seats > $num_candidates) {
      $must_be_elected = $num_candidates;
    }
    return $this->num_elected >= $must_be_elected;
  }

  /**
   * Calculate the minimum number of votes a candidate needs in order to be
   * elected.
   *
   * By default this is the Droop quota:
   *   floor((number of valid ballots / (number of seats + 1)) + 1)
   *
   * @return int
   */
  protected function _calculateQuota() {
    $election = $this->election;
    $num = ($election->getNumBallots() / ($election->getNumSeats() + 1)) + 1;
    $quota = floor($num);
    $this->quota = $quota;
    return $quota;
  }

}
