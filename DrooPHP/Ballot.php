<?php
/**
 * @file
 *   DrooPHP_Ballot class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Ballot
 *   Container for a ballot, i.e. an individual "ballot paper", cast by a single
 *   voter.
 */
class DrooPHP_Ballot {

  /**
   * The ranking, expressed as an array of candidate IDs keyed by their
   * preference level (e.g. the first preference candidate is keyed by 1).
   *
   * @var array
   */
  public $ranking = array();

  /**
   * A float or integer representing the value of this ballot.
   *
   * @var mixed
   */
  public $value;

  /**
   * Constructor - expects valid input but no validation is performed here, that
   * is left to other parts of the program.
   *
   * @param array $ranking
   * @param mixed $value
   *
   * @return void
   */
  public function __construct(Array $ranking, $value = 1) {
    $this->ranking = $ranking;
    $this->value = $value;
  }

}
