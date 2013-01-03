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
        $this->config = new Config($options, $this->getDefaultOptions());
        $this->election = new Election();
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
