<?php
/**
 * @file
 * Class representing a ballot.
 */

namespace DrooPHP;

class Ballot implements BallotInterface {

  protected $last_used_level;
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
      return array();
    }
    return (array) $this->ranking[$level];
  }

  /**
   * @{inheritdoc}
   */
  public function getLastPreference() {
    $level = $this->last_used_level ?: 0;
    return $this->getPreference($level);
  }

  /**
   * @{inheritdoc}
   */
  public function getNextPreference() {
    $level = $this->last_used_level ?: 0;
    return $this->getPreference($level + 1);
  }

  /**
   * @{inheritdoc}
   */
  public function getNextPreferenceWorth() {
    $level = $this->last_used_level ?: 0;
    if (!isset($this->ranking[$level + 1])) {
      return 0;
    }
    $vote = $this->ranking[$level + 1];
    return (1 / count($vote)) * $this->value;
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

  /**
   * @{inheritdoc}
   */
  public function setLastUsedLevel($level, $increment = FALSE) {
    if ($increment) {
      $level += $this->last_used_level;
    }
    $this->last_used_level = $level;
    return $this;
  }

}
