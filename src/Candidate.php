<?php
/**
 * @file
 * Class representing a candidate in an election.
 */

namespace DrooPHP;

class Candidate implements CandidateInterface {

  public $name;

  protected $id;
  protected $state;
  protected $surplus;
  protected $votes;

  /**
   * @{inheritdoc}
   */
  public function __construct($name, $id) {
    $this->name = $name;
    $this->id = $id;
    $this->state = self::STATE_HOPEFUL;
  }

  /**
   * @{inheritdoc}
   */
  public function getVotes() {
    return $this->votes;
  }

  /**
   * @{inheritdoc}
   */
  public function setVotes($votes, $increment = FALSE) {
    $this->votes = $increment ? $this->votes + $votes : $votes;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getSurplus() {
    return $this->surplus;
  }

  /**
   * @{inheritdoc}
   */
  public function setSurplus($amount, $increment = FALSE) {
    $this->surplus = $increment ? $this->surplus + $amount : $amount;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @{inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @{inheritdoc}
   */
  public function getState($formatted = FALSE) {
    return $formatted ? $this->getFormattedState() : $this->state;
  }

  /**
   * @{inheritdoc}
   */
  public function setState($state) {
    $this->state = $state;
    return $this;
  }

  /**
   * @{inheritdoc}
   */
  protected function getFormattedState() {
    switch ($this->state) {
      case self::STATE_DEFEATED:
        return 'Defeated';
      case self::STATE_WITHDRAWN:
        return 'Withdrawn';
      case self::STATE_ELECTED:
        return 'Elected';
      case self::STATE_HOPEFUL:
        return 'Hopeful';
    }
    return 'Unknown';
  }

}
