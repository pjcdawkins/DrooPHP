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
   * Run the count.
   *
   * @param ElectionInterface $election
   * @param int $stage
   */
  public function run(ElectionInterface $election, $stage = 1);

}
