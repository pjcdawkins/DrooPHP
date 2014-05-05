<?php
/**
 * @file
 * An interface for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Count;

interface FormatterInterface {

  /**
   * Constructor.
   */
  public function __construct(Count $count);

  /**
   * @return string
   */
  public function getOutput();

}
