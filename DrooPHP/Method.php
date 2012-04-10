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

  /** @var float */
  public $quota;

  /** @var int */
  public $num_elected;

  /**
   * @see self::logRound()
   * @var array
   */
  public $rounds = array();

  /**
   * Constructor
   *
   * @param DrooPHP_Count $count
   *   The object representing this count.
   */
  public function __construct(DrooPHP_Count $count) {
    $this->count = $count;
    $this->election = $count->election;
    $this->calculateQuota();
  }

  /**
   * Run the election: this is the main, iterative method.
   */
  public function run($round = 1) {
    echo "- COUNTING ROUND $round -\n"; // debugging
    $this->logRound($round);
  }

  /**
   * Log information about a voting round, e.g. the number of votes for each
   * candidate.
   *
   * @var int $round
   *
   * @return void
   */
  public function logRound($round) {
    if (isset($this->rounds[$round])) {
      throw new DrooPHP_Exception("Already written log for round $round.");
    }
    $this->rounds[$round] = array();
    $log = &$this->rounds[$round];
    $log = array(
      'votes' => array(), // array of votes keyed by candidate ID
      'state' => array(), // array of states keyed by candidate ID
    );
    foreach ($this->election->candidates as $cid => $candidate) {
      $log['votes'][$cid] = $candidate->votes;
      $log['state'][$cid] = $candidate->state;
    }
  }

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
    $num_seats = $election->num_seats;
    $num_candidates = $election->num_candidates;
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
  protected function calculateQuota() {
    $election = $this->election;
    $num = ($election->num_valid_ballots / ($election->num_seats + 1)) + 1;
    $quota = floor($num);
    $this->quota = $quota;
    return $quota;
  }

}
