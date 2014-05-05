<?php
/**
 * @file
 * Interface for an election count.
 */

namespace DrooPHP;

use DrooPHP\ElectionInterface;
use DrooPHP\Method\MethodInterface;
use DrooPHP\Source\SourceInterface;
use DrooPHP\Formatter\FormatterInterface;

interface CountInterface {

  /**
   * Run the count, and generate output.
   *
   * @return string
   */
  public function run();

  /**
   * Get the election object.
   *
   * @return ElectionInterface
   */
  public function getElection();

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
