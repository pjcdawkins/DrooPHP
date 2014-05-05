<?php
/**
 * @file
 * A base class for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Count;

/**
 * A base class for an output formatter.
 */
abstract class FormatterBase implements FormatterInterface {

  public $count;

  /**
   * Constructor.
   */
  public function __construct(Count $count) {
    $this->config = $count->config->addDefaultOptions($this->getDefaultOptions());
    $this->count = $count;
  }

  /**
   * Get an array of default config option values.
   *
   * @see self::__construct()
   */
  public function getDefaultOptions() {
    return array();
  }

}
