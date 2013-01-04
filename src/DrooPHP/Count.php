<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

/**
 * Main class for an election count.
 */
class Count
{

    public $config;

    protected $election;
    protected $formatter;
    protected $method;
    protected $source;

    /**
     * Constructor. Sets up configuration.
     *
     * @param mixed $config  Configuration: an array of options, or an object
     *                       whose class implements ConfigInterface.
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new Config($config);
        }
        else if (!$config instanceof ConfigInterface) {
            throw new \Exception('Invalid configuration');
        }
        $this->config = $config->addDefaultOptions($this->getDefaultOptions());
    }

    /**
     * Run the count, and generate output.
     *
     * @return string
     */
    public function run()
    {
        $method = $this->getMethod();
        $election = $this->getElection();
        $formatter = $this->getFormatter();
        $method->setElection($election)->run();
        $output = $formatter->getOutput();
        return $output;
    }

    /**
     * Get an array of default options.
     *
     * @return array
     */
    public function getDefaultOptions() {
        return array(
            'source' => 'file',
            'method' => 'wikipedia',
            'formatter' => 'html',
        );
    }

    /**
     * Get the election object.
     *
     * @return Election
     */
    public function getElection()
    {
        if (!$this->election) {
            $this->election = $this->getSource()->loadElection();
        }
        return $this->election;
    }

    /**
     * Get the source object.
     *
     * @return \DrooPHP\Source\SourceInterface
     */
    public function getSource()
    {
        if (!$this->source) {
            $option = $this->config->getOption('source');
            $namespace = __NAMESPACE__ . '\\Source';
            $interface_name = $namespace . '\\SourceInterface';
            if ($option = Utility::validateConfigOption($option, $interface_name, $namespace)) {
                $this->source = is_object($option) ? $option : new $option($this);
            }
            else {
                throw new \Exception(
                    '"source" must be an object or class name, implementing \DrooPHP\Source\SourceInterface.'
                );
            }
        }
        return $this->source;
    }

    /**
     * Get the counting method object.
     *
     * @return \DrooPHP\Method\MethodInterface
     */
    public function getMethod()
    {
        if (!$this->method) {
            $option = $this->config->getOption('method');
            $namespace = __NAMESPACE__ . '\\Method';
            $interface_name = $namespace . '\\MethodInterface';
            if ($option = Utility::validateConfigOption($option, $interface_name, $namespace)) {
                $this->method = is_object($option) ? $option : new $option($this);
            }
            else {
                throw new \Exception(
                    '"method" must be an object or class name, implementing \DrooPHP\Method\MethodInterface.'
                );
            }
        }
        return $this->method;
    }

    /**
     * Get the output format object.
     *
     * @return \DrooPHP\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $option = $this->config->getOption('formatter');
            $namespace = __NAMESPACE__ . '\\Formatter';
            $interface_name = $namespace . '\\FormatterInterface';
            if ($option = Utility::validateConfigOption($option, $interface_name, $namespace)) {
                $this->formatter = is_object($option) ? $option : new $option($this);
            }
            else {
                throw new \Exception(
                    '"formatter" must be an object or class name, implementing \DrooPHP\Formatter\FormatterInterface.'
                );
            }
        }
        return $this->formatter;
    }

}
