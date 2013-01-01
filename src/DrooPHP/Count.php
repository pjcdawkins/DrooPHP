<?php
namespace DrooPHP;

/**
 * Main class for a count, containing options and an election.
 */
class Count
{

    /** @var array */
    public $options = array();

    /** @var Election */
    public $election;

    /** @var Source */
    public $source;

    /**
     * Constructor: initiate a count.
     */
    public function __construct(Source $source, $options = array())
    {
        $this->loadOptions($options);
        $this->source = $source;
        $this->election = $source->loadElection();
    }

    /**
     * Get the default options for a count.
     *
     * Possible options:
     *   allow_invalid  Whether to continue counting despite encountering an
     *                  invalid or spoiled ballot.
     *   allow_equal    Whether to allow equal rankings (e.g. 2=3).
     *   allow_repeat   Whether to allow repeat rankings (e.g. 3 2 2).
     *   allow_skipped  Whether to allow skipped rankings (e.g. -).
     *   method         The name of a class extending Method.
     *   maxStages      The maximum number of counting stages.
     */
    public function getDefaultOptions()
    {
        return array(
            'allow_equal' => 0,
            'allow_skipped' => 0,
            'allow_repeat' => 0,
            'allow_invalid' => 1,
            'method' => 'Wikipedia',
            'maxStages' => 100,
        );
    }

    /**
     * Load options into the count, merging with default options.
     *
     * @param array $options
     */
    public function loadOptions(array $options = array())
    {
        $options = array_merge($this->getDefaultOptions(), $options);
        $this->options = $options;
    }

    /**
     * Get the value of an option.
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
