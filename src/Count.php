<?php
/**
 * @file
 * Main class for an election count.
 */

namespace DrooPHP;

use DrooPHP\Config;
use DrooPHP\Source\SourceInterface;
use DrooPHP\Method\MethodInterface;
use DrooPHP\Formatter\FormatterInterface;

class Count implements CountInterface, ConfigurableInterface {

  protected $config;
  protected $election;
  protected $formatter;
  protected $method;
  protected $source;

  /**
   * @{inheritdoc}
   */
  public function __construct(MethodInterface $method = NULL, SourceInterface $source = NULL, FormatterInterface $formatter = NULL) {
    $this->source = $source ?: new Source\File($this);
    $this->method = $method ?: new Method\Wikipedia($this);
    $this->formatter = $formatter ?: new Formatter\Text($this);
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
  }

  /**
   * @{inheritdoc}
   */
  public function run() {
    $election = $this->getElection();
    $method = $this->getMethod();
    $formatter = $this->getFormatter();
    $output = $formatter->getOutput();
    return $output;
  }

  /**
   * @{inheritdoc}
   */
  public function getElection() {
    if (!$this->election) {
      $this->election = $this->getSource()->loadElection();
    }
    return $this->election;
  }

  /**
   * @{inheritdoc}
   *
   * @throws \Exception
   */
  public function getSource() {
    if (!$this->source) {
      $option = $this->config->getOption('source');
      if (is_object($option)) {
        $this->source = $option;
      }
      else {
        $class_name = 'DrooPHP\\Source\\' . $option;
        $this->source = new $class_name($this);
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
        $this->method = new $class_name($this);
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
        $this->formatter = new $class_name($this);
      }
    }
    return $this->formatter;
  }

}
