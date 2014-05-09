<?php
/**
 * @file
 * Interface for a configurable.
 */

namespace DrooPHP\Config;

interface ConfigurableInterface {

  /**
   * Constructor.
   *
   * @param ConfigInterface|array $options
   */
  public function __construct($options = []);

  /**
   * Set options.
   *
   * @param array $options
   *
   * @return self
   */
  public function setOptions(array $options);

  /**
   * Get the default configuration options.
   *
   * @return array
   */
  public function getDefaults();

}
