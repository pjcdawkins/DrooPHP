<?php
/**
 * @file
 * Class to contain and manage configuration.
 */

namespace DrooPHP\Config;

class Config implements ConfigInterface {

  public $options = [];

  public $defaults = [];

  /**
   * @{inheritdoc}
   */
  public function __construct(array $options = [], array $defaults = []) {
    $this->setOptions($options);
    $this->addDefaults($defaults);
  }

  /**
   * @{inheritdoc}
   */
  public function addDefaults(array $defaults) {
    $this->defaults += $defaults;
    $this->options = array_merge($defaults, $this->options);
    return $this;
  }

  /**
   * @see ConfigInterface::setOptions()
   */
  public function setOptions(array $options) {
    $this->options = array_merge($this->defaults, $options);
    return $this;
  }

  /**
   * @see ConfigInterface::getOption()
   */
  public function getOption($key) {
    if (!isset($this->options[$key])) {
      return FALSE;
    }
    return $this->options[$key];
  }

  /**
   * @see ConfigInterface::setOption()
   */
  public function setOption($key, $value) {
    $this->options[$key] = $value;
    return $this;
  }

}
