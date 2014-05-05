<?php
/**
 * @file
 * A base class for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\CountInterface;

/**
 * A base class for an output formatter.
 */
abstract class FormatterBase implements FormatterInterface {

  public $count;

  /**
   * @{inheritdoc}
   */
  public function __construct(CountInterface $count) {
    $this->config = $count->getConfig()->addDefaultOptions($this->getDefaultOptions());
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
