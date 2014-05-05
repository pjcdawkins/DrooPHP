<?php
/**
 * @file
 * Interface for a vote counting method.
 */

namespace DrooPHP\Method;

use DrooPHP\CountInterface;

interface MethodInterface {

  /**
   * Constructor.
   */
  public function __construct(CountInterface $count);

  /**
   * Run the count.
   */
  public function run();

}
