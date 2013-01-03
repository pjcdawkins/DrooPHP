<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Config;

interface ConfigInterface
{

    /**
     * Constructor.
     */
    public function __construct(array $options = array(), array $defaults = array(), array $required = array());

    /**
     * Set the required options.
     *
     * @param array $keys
     */
    public function setRequiredOptions(array $keys);

    /**
     * Set default options and their values.
     *
     * @return array
     */
    public function setDefaultOptions(array $options);

    /**
     * Load user-supplied options, validating them, and merging with defaults.
     *
     * @param array $options
     */
    public function loadOptions(array $options);

    /**
     * Get the value of a single option.
     *
     * @param string $key  The option key.
     *
     * @return mixed
     *     The option value, or NULL.
     */
    public function getOption($key);

    /**
     * Set the value of a single option.
     *
     * @param string $key  The option key.
     * @param string $value  The option value.
     */
    public function setOption($key, $value);

}
