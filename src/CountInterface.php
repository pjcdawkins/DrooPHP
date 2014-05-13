<?php
/**
 * @file
 * Interface for an election count.
 */

namespace DrooPHP;

use DrooPHP\Method\MethodInterface;
use DrooPHP\Source\SourceInterface;
use DrooPHP\Formatter\FormatterInterface;

interface CountInterface {

  /**
   * Get formatted output. This runs the count (if it has not yet run).
   *
   * @return string
   */
  public function getOutput();

  /**
   * Get the result. This runs the count (if it has not yet run).
   *
   * @return ResultInterface
   */
  public function getResult();

  /**
   * Get the source object.
   *
   * @return SourceInterface
   */
  public function getSource();

  /**
   * Get the counting method object.
   *
   * @return MethodInterface
   */
  public function getMethod();

  /**
   * Get the output format object.
   *
   * @return FormatterInterface
   */
  public function getFormatter();

}
