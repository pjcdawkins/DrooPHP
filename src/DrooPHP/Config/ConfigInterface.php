<?php
namespace DrooPHP\Config;

interface ConfigInterface
{

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

    /**
     * Alias of self::getOption().
     */
    public function __get($key);

    /**
     * Alias of self::setOption().
     */
    public function __set($key, $value);

}
