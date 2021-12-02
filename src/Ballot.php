<?php
/**
 * @file
 * Class representing a ballot.
 */

namespace DrooPHP;

class Ballot implements BallotInterface {

  protected $identifier;
  protected $last_used_level = 0;
  protected $ranking = [];
  protected $value = 1;

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
  public function getIdentifier() {
    if (!isset($this->identifier)) {
      $this->identifier = '';
      foreach ($this->ranking as $preference) {
        if (is_array($preference)) {
          $preference = implode('=', $preference);
        }
        $this->identifier .= $preference . ' ';
      }
      $this->identifier = rtrim($this->identifier);
    }
    return $this->identifier;
  }

  /**
   * @{inheritdoc}
   */
  public function getPreference($level) {
    if (empty($this->ranking[$level])) {
      return [];
    }
    return (array) $this->ranking[$level];
  }

  /**
   * @{inheritdoc}
   */
  public function getLastPreference() {
    return $this->getPreference($this->last_used_level);
  }

  /**
   * @{inheritdoc}
   */
  public function getNextPreference() {
    return $this->getPreference($this->last_used_level + 1);
  }

  /**
   * @{inheritdoc}
   */
  public function getNextPreferenceWorth() {
    $level = $this->last_used_level ? : 0;
    if (empty($this->ranking[$level + 1])) {
      return 0;
    }
    $vote = $this->ranking[$level + 1];
    // FIXME assuming that when $vote is not an array, it would be counted as 1
    if (!is_array($vote)) $vote = [$vote];
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
  }

  /**
   * @{inheritdoc}
   */
  public function isExhausted() {
    return $this->last_used_level >= count($this->ranking);
  }

}
