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
use DrooPHP\Exception\UsageException;
use DrooPHP\Result;

abstract class MethodBase implements MethodInterface, ConfigurableInterface {

  protected $quota;
  protected $stages = [];
  protected $election;
  protected $result;

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
  public function getQuota() {
    return $this->quota;
  }

  /**
   * Log information about a voting stage, e.g. the number of votes for each
   * candidate.
   *
   * @param int $stage
   */
  public function logStage($stage) {
    if (!isset($this->stages[$stage])) {
      $this->stages[$stage] = ['votes' => [], 'state' => [], 'changes' => []];
    }
    $log = & $this->stages[$stage];
    foreach ($this->getElection()->getCandidates() as $cid => $candidate) {
      $log['votes'][$cid] = round($candidate->getVotes(), 2);
      $log['state'][$cid] = $candidate->getState(TRUE);
    }
  }

  /**
   * Log a change about a candidate.
   *
   * @param CandidateInterface $candidate
   * @param string $message
   * @param int $stage
   */
  public function logChange(CandidateInterface $candidate, $message, $stage) {
    if (!isset($this->stages[$stage])) {
      $this->stages[$stage] = ['votes' => [], 'state' => [], 'changes' => []];
    }
    if (!isset($this->stages[$stage]['changes'][$candidate->getId()])) {
      $this->stages[$stage]['changes'][$candidate->getId()] = [];
    }
    $this->stages[$stage]['changes'][$candidate->getId()][] = $message;
  }

  /**
   * Test whether the election is complete.
   *
   * @return bool
   */
  public function isComplete() {
    $election = $this->getElection();
    $num_seats = $election->getNumSeats();
    $num_candidates = $election->getNumCandidates();
    $must_be_elected = $num_seats;
    if ($num_seats > $num_candidates) {
      $must_be_elected = $num_candidates;
    }
    $filled_seats = count($election->getCandidates(CandidateInterface::STATE_ELECTED));
    return $filled_seats >= $must_be_elected;
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
   * @return Result
   */
  protected function getResult() {
    return new Result($this);
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
    $num = ($this->getElection()->getNumValidBallots() / ($this->getElection()->getNumSeats() + 1)) + 1;
    $quota = floor($num);
    $this->quota = $quota;
    return $quota;
  }

}
