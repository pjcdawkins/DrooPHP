<?php
/**
 * @file
 * Base class for a vote counting method.
 */

namespace DrooPHP\Method;

use DrooPHP\CandidateInterface;
use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\ElectionInterface;
use DrooPHP\Exception\CountException;
use DrooPHP\Exception\UsageException;

abstract class MethodBase implements MethodInterface, ConfigurableInterface {

  protected $quota;
  protected $stages = [];

  /** @var ElectionInterface */
  protected $election;

  use ConfigurableTrait;

  /**
   * @{inheritdoc}
   */
  public function getDefaults() {
    return [
      'allow_equal' => FALSE,
      'allow_skipped' => FALSE,
      'allow_repeat' => FALSE,
      'allow_invalid' => TRUE,
      'max_stages' => 100,
    ];
  }

  /**
   * @{inheritdoc}
   */
  public function setElection(ElectionInterface $election) {
    $this->election = $election;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getElection() {
    if (!isset($this->election)) {
      throw new UsageException('Election not defined');
    }
    if (!$this->election->getNumCandidates()) {
      throw new CountException('No candidates found');
    }
    if (!$this->election->getNumValidBallots()) {
      throw new CountException('No ballot found');
    }
    return $this->election;
  }

  /**
   * @{inheritdoc}
   */
  public function getStages() {
    return $this->stages;
  }

  /**
   * @{inheritdoc}
   */
  public function getQuota($recalculate = FALSE) {
    if (!isset($this->quota) || $recalculate) {
      $this->quota = $this->calculateQuota();
    }
    return $this->quota;
  }

  /**
   * Log information about a voting stage, e.g. the number of votes for each
   * candidate.
   *
   * @param int $stage
   */
  public function logStage($stage) {
    foreach ($this->getElection()->getCandidates() as $cid => $candidate) {
      $this->stages[$stage]['votes'][$cid] = round($candidate->getVotes(), 2);
      $this->stages[$stage]['changes'][$cid] = $candidate->getLog(TRUE);
    }
  }

  /**
   * Test whether the election is complete.
   *
   * @return bool
   */
  public function isComplete() {
    return $this->getNumVacancies() <= 0;
  }

  /**
   * Find the number of seats yet to be filled.
   *
   * @return int
   */
  public function getNumVacancies() {
    $election = $this->getElection();
    $filled_seats = count($election->getCandidates(CandidateInterface::STATE_ELECTED));
    return $election->getNumSeats() - $filled_seats;
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
    $election = $this->getElection();
    return floor(($election->getNumValidBallots() / ($election->getNumSeats() + 1)) + 1);
  }

}
