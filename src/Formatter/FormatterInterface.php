<?php
/**
 * @file
 * An interface for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\ElectionInterface;
use DrooPHP\Method\MethodInterface;

interface FormatterInterface {

  /**
   * @param MethodInterface $method
   * @param ElectionInterface $election
   *
   * @return string
   */
  public function getOutput(MethodInterface $method, ElectionInterface $election);

}
