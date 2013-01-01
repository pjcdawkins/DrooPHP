<?php
namespace DrooPHP;

use \DrooPHP\Config\ConfigInterface;

/**
 * A soure of election data.
 */
abstract class Source implements ConfigInterface
{

    /** @var array */
    public $options = array();

    /** @var Election */
    public $election;

    /**
     * Constructor.
     *
     * @see ConfigInterface::__construct()
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
    }

    /**
     * Load the election data.
     *
     * @return Election
     */
    public function loadElection() {
      $this->election = new Election;
      return $this->election;
    }

    /**
     * Set up options.
     *
     * @param array $options An associative array of options.
     */
    public function loadOptions(array $options = array())
    {
        $options = array_merge($this->getDefaultOptions(), $options);
        $this->options = $options;
    }

    /**
     * Get the default options.
     *
     * @see ConfigInterface::getDefaultOptions()
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * Get the value of an option.
     *
     * @see ConfigInterface::getOption()
     *
     * @param string $option The name of the option.
     * @param mixed $or A value to return if the option doesn't exist.
     *
     * @return mixed
     */
    public function getOption($option, $or = NULL)
    {
        if ($or !== NULL && !isset($this->options[$option])) {
            return $or;
        }
        return $this->options[$option];
    }

}
