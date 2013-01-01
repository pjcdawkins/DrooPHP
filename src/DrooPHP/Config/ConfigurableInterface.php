<?php
namespace DrooPHP\Config;

/**
 * Interface for objects that can be configured.
 */
interface ConfigurableInterface
{

    /**
     * Get the default options for this configurable.
     *
     * @return array
     */
    public function getDefaultOptions();

    /**
     * Get the required options for this configurable.
     *
     * @return array
     */
    public function getRequiredOptions();

}
