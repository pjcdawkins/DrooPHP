<?php
/**
 * @file
 * A base class for a source of election data.
 */

namespace DrooPHP\Source;

use DrooPHP\Config\Config;
use DrooPHP\Config\ConfigInterface;
use DrooPHP\Config\ConfigurableInterface;

/**
 * A base class for a source of election data.
 */
abstract class SourceBase implements SourceInterface, ConfigurableInterface {

  /** @var ConfigInterface */
  protected $config;

  /**
   * Constructor.
   *
   * @param array $options
   *   Configuration options.
   */
  public function __construct(array $options = []) {
    $this->getConfig()
      ->addDefaults($this->getDefaults())
      ->setOptions($options);
  }

  /**
   * Get default options.
   *
   * @return array
   */
  public function getDefaults() {
    return [
      'allow_equal' => FALSE,
      'allow_skipped' => FALSE,
      'allow_repeat' => FALSE,
      'allow_invalid' => FALSE,
    ];
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
   *
   * Possible options:
   *   allow_invalid  bool    Whether to continue loading despite encountering
   *                          invalid or spoiled ballots.
   *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
   *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
   *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
   */
  public function setOptions(array $options) {
    $this->getConfig()->setOptions($options);
    return $this;
  }

}
