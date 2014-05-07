<?php
/**
 * @file
 * Main class for an election count.
 */

namespace DrooPHP;

use DrooPHP\Config\ConfigurableInterface;

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
  public function __construct(array $options = []) {
    $this->getConfig()
      ->addDefaults($this->getDefaults())
      ->setOptions($options);
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
    $this->getConfig()->setOptions($options);
    return $this;
  }

  /**
   * @return array
   */
  public function getDefaults() {
    return [
      'formatter' => 'Html',
      'method' => 'Wikipedia',
      'source' => 'File',
    ];
  }

  /**
   * @{inheritdoc}
   */
  public function run() {
    $method = $this->getMethod();
    $method->setElection($this->getSource()->loadElection());
    $method->run();
    $output = $this->getFormatter()->getOutput($method);
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
      $option = $this->getConfig()->getOption('method');
      if (is_object($option)) {
        $this->method = $option;
      }
      else {
        $class_name = 'DrooPHP\\Method\\' . $option;
        $this->method = new $class_name();
      }
    }
    return $this->method;
  }

  /**
   * @{inheritdoc}
   */
  public function getFormatter() {
    if (!$this->formatter) {
      $option = $this->getConfig()->getOption('formatter');
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
