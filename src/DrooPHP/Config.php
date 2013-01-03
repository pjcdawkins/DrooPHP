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

    public $defaults = array();

    /**
     * Constructor.
     */
    public function __construct(array $options = array(), array $defaults = array())
    {
        $this->setOptions($options);
        $this->addDefaultOptions($defaults);
    }

    /**
     * @see ConfigInterface::addDefaultOptions()
     */
    public function addDefaultOptions(array $defaults)
    {
        $this->defaults += $defaults;
        $this->options = array_merge($defaults, $this->options);
        return $this;
    }

    /**
     * @see ConfigInterface::setOptions()
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaults, $options);
        return $this;
    }

    /**
     * @see ConfigInterface::getOption()
     */
    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            return FALSE;
        }
        return $this->options[$key];
    }

    /**
     * @see ConfigInterface::setOption()
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

}
