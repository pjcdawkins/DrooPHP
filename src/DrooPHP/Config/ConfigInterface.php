<?php
namespace DrooPHP\Config;

interface ConfigInterface
{

    /**
     * Constructor. Allows passing in an array of options.
     */
    public function __construct(array $options = array());

    /**
     * Load options into the object, merging with default options.
     *
     * @param array $options
     */
    public function loadOptions(array $options = array());

    /**
     * Get default options.
     *
     * @return array
     */
    public function getDefaultOptions();

    /**
     * Get the value of an option.
     *
     * @param string $option  The name of the option.
     * @param mixed $or       A value to return if the option doesn't exist.
     *
     * @return mixed
     */
    public function getOption($name, $or = NULL);

}
