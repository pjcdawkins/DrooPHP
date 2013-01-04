<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

use \DrooPHP\Source\SourceInterface;

/**
 * A base class for a source of election data.
 */
abstract class Source implements SourceInterface
{

    /** @var ConfigInterface */
    public $config;

    /**
     * Constructor.
     */
    public function __construct(Count $count)
    {
        $this->config = $count->config;
        $this->config->addDefaultOptions($this->getDefaultOptions());
    }

    /**
     * Get an array of default config option values.
     *
     * Possible options:
     *   allow_invalid  bool    Whether to continue loading despite encountering
     *                          invalid or spoiled ballots.
     *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
     *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
     *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
     *
     * @see self::__construct()
     */
    public function getDefaultOptions()
    {
        return array(
            'allow_equal' => FALSE,
            'allow_skipped' => FALSE,
            'allow_repeat' => FALSE,
            'allow_invalid' => FALSE,
        );
    }

}
