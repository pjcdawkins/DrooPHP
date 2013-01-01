<?php
namespace DrooPHP;

use \DrooPHP\Config\ConfigInterface;

/**
 * Main class for a count, containing options and an election.
 */
class Count implements ConfigInterface
{

    /** @var array */
    public $options = array();

    /** @var Election */
    public $election;

    /**
     * Constructor: initiate a count.
     *
     * @see ConfigInterface::__construct()
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
        $this->election = $this->getOption('source')->loadElection();
    }

    /**
     * Get the default options for a count.
     *
     * @see ConfigInterface::getDefaultOptions()
     *
     * Possible options:
     *   source         Source  Required: an object whose class extends
     *                          \DrooPHP\Source.
     *   allow_invalid  bool    Whether to continue counting despite
     *                          encountering an invalid or spoiled ballot.
     *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
     *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
     *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
     *   method         string  The absolute name of a class extending
     *                          \DrooPHP\Method.
     *   maxStages      string  The maximum number of counting stages.
     */
    public function getDefaultOptions()
    {
        return array(
            'source' => NULL,
            'allow_equal' => 0,
            'allow_skipped' => 0,
            'allow_repeat' => 0,
            'allow_invalid' => 1,
            'method' => 'Wikipedia',
            'maxStages' => 100,
        );
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
    public function run() {
        $method = $this->getMethod();
        $method->run();
        return $method;
    }

    /**
     * Get the Method object. This will do the counting work.
     *
     * @see Method
     *
     * @return Method
     *     An object whose class implements \DrooPHP\Method.
     */
    public function getMethod() {
        static $method;
        if ($method === NULL) {
            $method_option = $this->getOption('method');
            if ($method_option instanceof Method) {
                $method = $method_option;
            }
            else if (is_string($method_option)) {
                $class_name = $method_option;
                // Allow $method_option to be an unqualified class name relative
                // to \DrooPHP\Method.
                if (!class_exists($class_name)) {
                    $class_name = '\\DrooPHP\\Method\\' . $class_name;
                }
                // Ensure that the class extends \DrooPHP\Method.
                if (class_exists($class_name) && ($parents = class_parents($class_name)) && in_array(__NAMESPACE__ . '\\Method', $parents)) {
                    // Load the Method object, passing in this Count object.
                    $method = new $class_name($this);
                }
            }
            if (!$method) {
                throw new \Exception('Invalid value provided for option "method".');
            }
        }
        return $method;
    }

    /**
     * Load options into the count, merging with default options.
     *
     * @see ConfigInterface::loadOptions()
     *
     * @param array $options
     */
    public function loadOptions(array $options = array())
    {
        // The source is mandatory.
        if (empty($options['source'])) {
            throw new \Exception('You must specify a source.');
        }
        if (!$options['source'] instanceof Source) {
            throw new \Exception('The source must extend \DrooPHP\Source.');
        }
        $options = array_merge($this->getDefaultOptions(), $options);
        $this->options = $options;
    }

    /**
     * @see ConfigInterface::getOption()
     */
    public function getOption($option, $or = NULL)
    {
        if ($or !== NULL && !isset($this->options[$option])) {
            return $or;
        }
        return $this->options[$option];
    }

}
