<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

use \DrooPHP\Source\SourceInterface;

/**
 * A source of election data.
 */
abstract class Source implements SourceInterface
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
        $config = new Config($options, $this->getDefaultOptions());
        $this->config = $config;
        $this->election = new Election();
    }

    /**
     * Get an array of default config option values.
     *
     * @see self::__construct()
     */
    public function getDefaultOptions()
    {
        return array();
    }

}
