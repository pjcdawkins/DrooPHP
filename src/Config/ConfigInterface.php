<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Config;

interface ConfigInterface {

  /**
   * Constructor.
   */
  public function __construct(array $options = array(), array $defaults = array());

  /**
   * Set default options and their values.
   *
   * Default options are merged with given options.
   *
   * @param array $defaults
   *
   * @return self
   */
  public function addDefaultOptions(array $defaults);

  /**
   * Set the user-supplied options. These are merged with defaults.
   *
   * @param array $options
   *
   * @return self
   */
  public function setOptions(array $options);

  /**
   * Get the value of a single option.
   *
   * @param string $key The option key.
   *
   * @return mixed
   *     The option value, or NULL.
   */
  public function getOption($key);

  /**
   * Set the value of a single option.
   *
   * @param string $key The option key.
   * @param string $value The option value.
   */
  public function setOption($key, $value);

}
