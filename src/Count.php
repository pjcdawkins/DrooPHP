<?php
/**
 * @file
 * Main class for an election count.
 */

namespace DrooPHP;

use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\Exception\ConfigException;

class Count implements CountInterface, ConfigurableInterface {

  protected $election;
  protected $formatter;
  protected $method;
  protected $result;
  protected $source;

  use ConfigurableTrait;

  /**
   * @return array
   */
  public function getDefaults() {
    return [
      'formatter' => 'Html',
      'method' => 'Stv',
      'source' => 'File',
    ];
  }

  /**
   * @{inheritdoc}
   */
  protected function runCount() {
    $method = $this->getMethod();
    $method->setElection($this->getSource()->loadElection());
    $method->run();
    $this->result = new Result($method);
  }

  /**
   * @{inheritdoc}
   */
  public function getResult() {
    if (!isset($this->result)) {
      $this->runCount();
    }
    return $this->result;
  }

  /**
   * @{inheritdoc}
   */
  public function getOutput() {
    $formatter = $this->getFormatter();
    return $formatter->getOutput($this->getResult());
  }

  /**
   * @{inheritdoc}
   *
   * @throws ConfigException
   */
  public function getSource() {
    if (!$this->source) {
      $option = $this->getConfig()->getOption('source');
      if (is_object($option)) {
        $this->source = $option;
      }
      else {
        $class_name = 'DrooPHP\\Source\\' . ucfirst(strtolower($option));
        if (!class_exists($class_name)) {
          throw new ConfigException('Invalid source name: ' . $option);
        }
        $this->source = new $class_name();
      }
    }
    return $this->source;
  }

  /**
   * @{inheritdoc}
   *
   * @throws ConfigException
   */
  public function getMethod() {
    if (!$this->method) {
      $option = $this->getConfig()->getOption('method');
      if (is_object($option)) {
        $this->method = $option;
      }
      else {
        $class_name = 'DrooPHP\\Method\\' . ucfirst(strtolower($option));
        if (!class_exists($class_name)) {
          throw new ConfigException('Invalid method name: ' . $option);
        }
        $this->method = new $class_name();
      }
    }
    return $this->method;
  }

  /**
   * @{inheritdoc}
   *
   * @throws ConfigException
   */
  public function getFormatter() {
    if (!$this->formatter) {
      $option = $this->getConfig()->getOption('formatter');
      if (is_object($option)) {
        $this->formatter = $option;
      }
      else {
        $class_name = 'DrooPHP\\Formatter\\' . ucfirst(strtolower($option));
        if (!class_exists($class_name)) {
          throw new ConfigException('Invalid formatter name: ' . $option);
        }
        $this->formatter = new $class_name();
      }
    }
    return $this->formatter;
  }

}
