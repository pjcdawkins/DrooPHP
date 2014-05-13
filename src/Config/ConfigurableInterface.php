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

  /**
   * Magic setter function for easily setting an option.
   *
   * @param string $key
   * @param mixed $value
   */
  public function __set($key, $value);

}
