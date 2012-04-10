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

  /** @var float */
  public $quota;

  /**
   * @see self::logStage()
   * @var array
   */
  public $stages = array();

  /** @var DrooPHP_Count */
  public $count;

  /**
   * Constructor
   *
   * @param DrooPHP_Count $count
   *   The object representing this count.
   */
  public function __construct(DrooPHP_Count $count) {
    $this->count = $count;
    $this->calculateQuota();
  }

  /**
   * Run the election: this is the main, iterative method.
   */
  abstract public function run($stage = 1);

  /**
   * Log information about a voting stage, e.g. the number of votes for each
   * candidate.
   *
   * @var int $stage
   *
   * @return void
   */
  public function logStage($stage) {
    if (isset($this->stages[$stage])) {
      throw new DrooPHP_Exception("Already written log for stage $stage.");
    }
    $this->stages[$stage] = array();
    $log = &$this->stages[$stage];
    $log = array(
      'votes' => array(), // array of votes keyed by candidate ID
      'state' => array(), // array of states keyed by candidate ID
    );
    foreach ($this->count->election->candidates as $cid => $candidate) {
      $log['votes'][$cid] = $candidate->votes;
      $log['state'][$cid] = $candidate->state;
    }
  }

  /**
   * Test whether the election is complete.
   *
   * @return bool
   */
  public function isComplete() {
    $election = $this->count->election;
    $num_seats = $election->num_seats;
    $num_candidates = $election->num_candidates;
    $must_be_elected = $num_seats;
    if ($num_seats > $num_candidates) {
      $must_be_elected = $num_candidates;
    }
    return $election->num_filled_seats >= $must_be_elected;
  }

  /**
   * Find the number of seats yet to be filled.
   *
   * @return int
   */
  public function getNumVacancies() {
    $election = $this->count->election;
    return $election->num_seats - $election->num_filled_seats;
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
  protected function calculateQuota() {
    $election = $this->count->election;
    $num = ($election->num_valid_ballots / ($election->num_seats + 1)) + 1;
    $quota = floor($num);
    $this->quota = $quota;
    return $quota;
  }

}
