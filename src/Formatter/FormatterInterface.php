<?php
/**
 * @file
 * An interface for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Method\MethodInterface;

interface FormatterInterface {

  /**
   * @param MethodInterface $method
   *
   * @return string
   */
  public function getOutput(MethodInterface $method);

}
