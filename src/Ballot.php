<?php
/**
 * @file
 * Class representing a ballot.
 */

namespace DrooPHP;

class Ballot implements BallotInterface {

  protected $ranking;
  protected $value;

  /**
   * @{inheritdoc}
   */
  public function __construct(array $ranking, $value = 1) {
    $this->ranking = $ranking;
    $this->value = $value;
  }

  /**
   * @{inheritdoc}
   */
  public function getRanking() {
    return $this->ranking;
  }

  /**
   * @{inheritdoc}
   */
  public function getPreference($level) {
    if (!isset($this->ranking[$level])) {
      return FALSE;
    }
    return (array) $this->ranking[$level];
  }

  /**
   * @{inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @{inheritdoc}
   */
  public function addValue($amount) {
    $this->value += $amount;
  }

}
