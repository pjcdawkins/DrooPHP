<?php
/**
 * @file
 * A base class for a source of election data.
 */

namespace DrooPHP\Source;

use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;

/**
 * A base class for a source of election data.
 */
abstract class SourceBase implements SourceInterface, ConfigurableInterface {

  use ConfigurableTrait;

  /**
   * @{inheritdoc}
   *
   * Possible options:
   *   allow_invalid  bool    Whether to continue loading despite encountering
   *                          invalid or spoiled ballots.
   *   allow_empty    bool    Whether to allow empty ballots (default: TRUE).
   *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
   *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
   *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
   */
  public function getDefaults() {
    return [
      'allow_invalid' => FALSE,
      'allow_empty' => TRUE,
      'allow_equal' => FALSE,
      'allow_skipped' => FALSE,
      'allow_repeat' => FALSE,
    ];
  }

}
