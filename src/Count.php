<?php
/**
 * @file
 * Main class for an election count.
 */

namespace DrooPHP;

use DrooPHP\Config;
use DrooPHP\Source\File;

class Count implements CountInterface, ConfigurableInterface {

  protected $config;
  protected $election;
  protected $formatter;
  protected $method;
  protected $source;

  /**
   * Constructor.
   *
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct(array $options = array()) {
    $this->getConfig()
      ->addDefaultOptions($this->getDefaultOptions())
      ->setOptions($options);
    if (isset($options['filename'])) {
      $source = $this->getSource();
      if (!$source instanceof File) {
        throw new \Exception('Cannot set filename');
      }
      $source->setOptions(array('filename' => $options['filename']));
      unset($options['filename']);
    }
  }

  /**
   * @{inheritdoc}
   */
  public function getConfig() {
    if (!$this->config) {
      $this->config = new Config();
    }
    return $this->config;
  }

  /**
   * @{inheritdoc}
   */
  public function setOptions(array $options) {
    $this->config->setOptions($options);
    return $this;
  }

  /**
   * @return array
   */
  public function getDefaultOptions() {
    return array(
      'formatter' => new Formatter\Html(),
      'method' => new Method\Wikipedia(),
      'source' => new Source\File(),
    );
  }

  /**
   * @{inheritdoc}
   */
  public function run() {
    $method = $this->getMethod();
    $election = $this->getSource()->loadElection();
    $method->run($election);
    $output = $this->getFormatter()->getOutput($method, $election);
    return $output;
  }

  /**
   * @{inheritdoc}
   *
   * @throws \Exception
   */
  public function getSource() {
    if (!$this->source) {
      $option = $this->getConfig()->getOption('source');
      if (is_object($option)) {
        $this->source = $option;
      }
      else {
        $class_name = 'DrooPHP\\Source\\' . $option;
        $this->source = new $class_name();
      }
    }
    return $this->source;
  }

  /**
   * @{inheritdoc}
   */
  public function getMethod() {
    if (!$this->method) {
      $option = $this->config->getOption('method');
      if (is_object($option)) {
        $this->method = $option;
      }
      else {
        $class_name = 'DrooPHP\\Method\\' . $option;
        $this->method = new $class_name($this->getElection());
      }
    }
    return $this->method;
  }

  /**
   * @{inheritdoc}
   */
  public function getFormatter() {
    if (!$this->formatter) {
      $option = $this->config->getOption('formatter');
      if (is_object($option)) {
        $this->formatter = $option;
      }
      else {
        $class_name = 'DrooPHP\\Formatter\\' . $option;
        $this->formatter = new $class_name();
      }
    }
    return $this->formatter;
  }

}
