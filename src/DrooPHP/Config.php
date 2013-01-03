<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

use \DrooPHP\Config\ConfigInterface;

class Config implements ConfigInterface
{

    public $options = array();

    public $default_options = array();

    public $required_options = array();

    /**
     * Constructor.
     */
    public function __construct(array $options = array(), array $defaults = array(), array $required = array())
    {
        $this->setDefaultOptions($defaults);
        $this->setRequiredOptions($required);
        if ($options) {
          $this->loadOptions($options);
        }
    }

    /**
     * @see ConfigInterface::setRequiredOptions()
     */
    public function setRequiredOptions(array $keys)
    {
        $this->required_options = $keys;
        return $this;
    }

    /**
     * @see ConfigInterface::setDefaultOptions()
     */
    public function setDefaultOptions(array $options)
    {
        $this->default_options = $options;
        return $this;
    }

    /**
     * @see ConfigInterface::loadOptions()
     *
     * @param array $options
     */
    public function loadOptions(array $options)
    {
        // Get default options.
        $defaults = $this->default_options;
        $options = array_merge($defaults, $options);
        // Check required options.
        $missing = array_diff($this->required_options, array_keys($options));
        if (count($missing)) {
            throw new \Exception(sprintf('Missing required option(s): %s.', implode(', ', $missing)));
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Get the value of a single option.
     *
     * @param string $key  The option key.
     *
     * @return mixed
     *     The option value, or FALSE if it doesn't exist.
     */
    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            return FALSE;
        }
        return $this->options[$key];
    }

    /**
     * Set the value of a single option.
     *
     * @param string $key  The option key.
     * @param string $value  The option value.
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

}
