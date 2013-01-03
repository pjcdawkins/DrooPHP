<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

use \DrooPHP\Formatter\FormatterInterface;

/**
 * A base class for an output formatter.
 */
abstract class Formatter implements FormatterInterface
{

    public $count;

    /**
     * Constructor.
     */
    public function __construct(Count $count)
    {
        $this->config = $count->config->addDefaultOptions($this->getDefaultOptions());
        $this->count = $count;
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
