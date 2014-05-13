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
   */
  public function getDefaults() {
    return [
      'allow_empty' => TRUE,
      'allow_equal' => FALSE,
      'allow_skipped' => FALSE,
      'allow_repeat' => FALSE,
      'allow_invalid' => FALSE,
    ];
  }

}
