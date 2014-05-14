<?php
/**
 * @file
 * Trait for configurables.
 */

namespace DrooPHP\Config;

use DrooPHP\Config;

trait ConfigurableTrait {

  protected $config;

  /**
   * @param array $options
   */
  public function __construct($options = []) {
    if ($options instanceof ConfigInterface) {
      $this->config = $options;
    }
    else {
      $this->config = new Config();
      if ($options) {
        $this->config->setOptions($options);
      }
    }
    $defaults = $this->getDefaults();
    if ($defaults) {
      $this->config->addDefaults($defaults);
    }
  }

  /**
   * @return ConfigInterface
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @{inheritdoc}
   */
  public function setOptions(array $options) {
    $this->config->setOptions($options);
    return $this;
  }

  /**
   * @return array
   */
  public function getDefaults() {
    return [];
  }

  /**
   * @{inheritdoc}
   */
  public function setOption($key, $value) {
    $this->config->setOption($key, $value);
    return $this;
  }

}
