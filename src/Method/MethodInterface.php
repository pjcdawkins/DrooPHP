<?php
/**
 * @file
 * Interface for a vote counting method.
 */

namespace DrooPHP\Method;

use DrooPHP\ElectionInterface;

interface MethodInterface {

  /**
   * Get the method name.
   *
   * @return string
   */
  public function getName();

  /**
   * Set the election to be processed.
   *
   * @param ElectionInterface $election
   *
   * @return self
   */
  public function setElection(ElectionInterface $election);

  /**
   * Get the election.
   *
   * @return ElectionInterface
   */
  public function getElection();

  /**
   * Run the count.
   *
   * @param int $stage
   */
  public function run($stage = 1);

  /**
   * Get the count stages.
   *
   * @return array
   */
  public function getStages();

  /**
   * Get the count quota.
   *
   * @return int|float
   */
  public function getQuota();

}
