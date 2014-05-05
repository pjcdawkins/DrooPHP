<?php
/**
 * @file
 * Interface for a configurable.
 */

namespace DrooPHP;

use DrooPHP\Config\ConfigInterface;

interface ConfigurableInterface {

  /**
   * Get the configuration for this object.
   *
   * @return ConfigInterface
   */
  public function getConfig();

  /**
   * Set options.
   *
   * @param array $options
   *
   * @return self
   */
  public function setOptions(array $options);

}
