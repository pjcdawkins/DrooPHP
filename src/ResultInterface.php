<?php
/**
 * @file
 * Interface for election results.
 */

namespace DrooPHP;

use DrooPHP\Method\MethodInterface;

interface ResultInterface {

  /**
   * Constructor.
   *
   * @param MethodInterface $method
   */
  public function __construct(MethodInterface $method);

  /**
   * Get the election.
   *
   * @return ElectionInterface
   */
  public function getElection();

  /**
   * Get the name of the counting method.
   *
   * @return string
   */
  public function getMethodName();

  /**
   * Get the stages of the count.
   *
   * @return array
   */
  public function getStages();

  /**
   * Get the quota of the count.
   *
   * @return int|float
   */
  public function getQuota();

  /**
   * Get the precision (number of decimal places) of numbers for display.
   *
   * @return int
   */
  public function getPrecision();

}
