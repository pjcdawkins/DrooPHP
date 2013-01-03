<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

use \DrooPHP\Config;
use \DrooPHP\Method\MethodInterface;
use \DrooPHP\Source\SourceInterface;

/**
 * Main class for a count, containing configuration and an election.
 */
class Count
{

    /** @var SourceInterface */
    public $source;

    /** @var MethodInterface */
    public $method;

    /** @var Election */
    public $election;

    /**
     * Constructor.
     *
     * @param SourceInterface $source   The source of the election data.
     * @param MethodInterface $method   The counting method.
     * @param array $options            An array of options to pass to $config.
     *
     * Possible options:
     *   allow_invalid  bool    Whether to continue counting despite
     *                          encountering an invalid or spoiled ballot.
     *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
     *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
     *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
     *   max_stages      string  The maximum number of counting stages.
     *
     * @todo sort this out
     */
    public function __construct(SourceInterface $source, MethodInterface $method, array $options = array())
    {
        $this->source = $source;
        $this->method = $method;
        if ($options) {
             $source->config->loadOptions($options);
             $method->config->loadOptions($options);
        }
    }

    /**
     * Run the count.
     *
     * @todo this should return something like an Output object
     *
     * @see Method::run()
     *
     * @return Method
     */
    public function run()
    {
        $method = $this->method;
        $method->election = $this->source->loadElection();
        $method->run();
        return $method;
    }


}
