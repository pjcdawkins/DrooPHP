<?php
/**
 * @file
 * Class to store results of a count.
 */

namespace DrooPHP;

use DrooPHP\Method\MethodInterface;

class Result implements ResultInterface {

  protected $method;

  /**
   * @{inheritdoc}
   */
  public function __construct(MethodInterface $method) {
    $this->method = $method;
  }

  /**
   * @{inheritdoc}
   */
  public function getElection() {
    return $this->method->getElection();
  }

  /**
   * Get the name of the counting method.
   *
   * @return string
   */
  public function getMethodName() {
    return $this->method->getName();
  }

  /**
   * @{inheritdoc}
   */
  public function getNumCandidates() {
    return count($this->method->getElection()->getCandidates());
  }

  /**
   * @{inheritdoc}
   */
  public function getElected() {
    return $this->method
      ->getElection()
      ->getCandidates(CandidateInterface::STATE_ELECTED);
  }

  /**
   * @{inheritdoc}
   */
  public function getStages() {
    return $this->method->getStages();
  }

  /**
   * @{inheritdoc}
   */
  public function getQuota() {
    return $this->method->getQuota();
  }

  /**
   * @{inheritdoc}
   */
  public function getPrecision() {
    return $this->method->getPrecision();
  }

}
