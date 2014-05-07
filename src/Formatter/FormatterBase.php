<?php
/**
 * @file
 * A base class for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Config\Config;
use DrooPHP\Config\ConfigurableInterface;

/**
 * A base class for an output formatter.
 */
abstract class FormatterBase implements FormatterInterface, ConfigurableInterface {

  protected $config;

  /**
   * @{inheritdoc}
   */
  public function __construct() {
    $this->getConfig()->addDefaults($this->getDefaults());
  }

  /**
   * @{inheritdoc}
   */
  public function getConfig() {
    if (!$this->config) {
      $this->config = new Config();
    }
    return $this->config;
  }

  /**
   * @{inheritdoc}
   */
  public function setOptions(array $options) {
    $this->getConfig()->setOptions($options);
    return $this;
  }

  /**
   * Get an array of default config option values.
   *
   * @see self::__construct()
   */
  public function getDefaults() {
    return [];
  }

}