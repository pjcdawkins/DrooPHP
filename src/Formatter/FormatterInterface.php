<?php
/**
 * @file
 * An interface for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\CountInterface;

interface FormatterInterface {

  /**
   * Constructor.
   */
  public function __construct(CountInterface $count);

  /**
   * @return string
   */
  public function getOutput();

}
