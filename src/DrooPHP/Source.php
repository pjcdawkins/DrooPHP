<?php
namespace DrooPHP;

use \DrooPHP\Config\ConfigurableInterface;
use \DrooPHP\Source\SourceInterface;

/**
 * A source of election data.
 */
abstract class Source implements SourceInterface, ConfigurableInterface
{

    /** @var Config */
    public $config;

    /** @var Election */
    public $election;

    /**
     * Constructor.
     */
    public function __construct(array $options = array())
    {
        $config = new Config($this);
        $config->loadOptions($options);
        $this->config = $config;
        $this->election = new Election();
    }

    /**
     * @see ConfigurableInterface::getDefaultOptions()
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * @see ConfigurableInterface::getRequiredOptions()
     */
    public function getRequiredOptions()
    {
        return array();
    }

}
