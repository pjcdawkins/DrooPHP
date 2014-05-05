<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

use DrooPHP\Candidate;
use DrooPHP\Config\ConfigInterface;
use DrooPHP\Count;
use DrooPHP\Election;

/**
 * Base class for a vote counting algorithm.
 */
abstract class MethodBase implements MethodInterface {

  /** @var int|float */
  public $quota;

  /**
   * @see self::logStage()
   * @var array
   */
  public $stages = array();

  /** @var Election */
  public $election;

  /** @var ConfigInterface */
  public $config;

  /**
   * @see MethodInterface::__construct()
   */
  public function __construct(Count $count) {
    $this->config = $count->config;
    $this->config->addDefaultOptions($this->getDefaultOptions());
  }

  /**
   * Get an array of default config option values.
   *
   * @see self::__construct()
   */
  public function getDefaultOptions() {
    return array(
      'allow_equal' => FALSE,
      'allow_skipped' => FALSE,
      'allow_repeat' => FALSE,
      'allow_invalid' => TRUE,
      'max_stages' => 100,
    );
  }

  /**
   * @see MethodInterface::setElection()
   */
  public function setElection(Election $election) {
    $this->election = $election;
    return $this;
  }

  /**
   * Log information about a voting stage, e.g. the number of votes for each
   * candidate.
   *
   * @param int $stage
   */
  public function logStage($stage) {
    if (!isset($this->stages[$stage])) {
      $this->stages[$stage] = array(
        'votes' => array(),
        'state' => array(),
        'changes' => array(),
      );
    }
    $log = & $this->stages[$stage];
    foreach ($this->election->candidates as $cid => $candidate) {
      $log['votes'][$cid] = round($candidate->votes, 2);
      $log['state'][$cid] = $candidate->getFormattedState();
    }
  }

  /**
   * Log a change about a candidate.
   *
   * @param Candidate $candidate
   * @param string $message
   * @param int $stage
   */
  public function logChange(Candidate $candidate, $message, $stage) {
    if (!isset($this->stages[$stage])) {
      $this->stages[$stage] = array(
        'votes' => array(),
        'state' => array(),
        'changes' => array(),
      );
    }
    if (!isset($this->stages[$stage]['changes'][$candidate->cid])) {
      $this->stages[$stage]['changes'][$candidate->cid] = array();
    }
    $this->stages[$stage]['changes'][$candidate->cid][] = $message;
  }

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
    return $election->num_filled_seats >= $must_be_elected;
  }

  /**
   * Find the number of seats yet to be filled.
   *
   * @return int
   */
  public function getNumVacancies() {
    $election = $this->election;
    return $election->num_seats - $election->num_filled_seats;
  }

  /**
   * Calculate the minimum number of votes a candidate needs in order to be
   * elected.
   *
   * By default this is the Droop quota:
   *     floor((number of valid ballots / (number of seats + 1)) + 1)
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
