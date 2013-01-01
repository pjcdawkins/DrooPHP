<?php
namespace DrooPHP;

/**
 * A soure of election data.
 */
abstract class Source
{

    /** @var array */
    public $options = array();

    /** @var Election */
    public $election;

    /**
     * Constructor.
     */
    public function __construct($options = array())
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
     * @return array
     * An associative array of default options.
     */
    public function getDefaultOptions()
    {
        return array();
    }

}
