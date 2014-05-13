<?php
/**
 * @file
 * Interface for a vote counting method.
 */

namespace DrooPHP\Method;

use DrooPHP\ElectionInterface;
use DrooPHP\Exception\CountException;

interface MethodInterface {

  /**
   * Set the election to be processed.
   *
   * @param ElectionInterface $election
   */
  public function setElection(ElectionInterface $election);

  /**
   * Get the method name.
   *
   * @return string
   */
  public function getName();

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
   *
   * @throws CountException
   *
   * @return bool
   *   TRUE if the count runs successfully, FALSE otherwise.
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
   * @param bool $recalculate
   *
   * @return int|float
   */
  public function getQuota($recalculate = FALSE);

  /**
   * Get the precision of values (for display).
   *
   * @return int
   *   The number of decimal places to display.
   */
  public function getPrecision();

}
