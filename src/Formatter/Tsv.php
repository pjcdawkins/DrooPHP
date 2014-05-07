<?php
/**
 * @file
 * A TSV (tab-separated values) output formatter.
 */

namespace DrooPHP\Formatter;

class Tsv extends Csv {

  /**
   * @{inheritdoc}
   */
  public function getDefaults() {
    $defaults = parent::getDefaults();
    $defaults['delimiter'] = "\t";
    return $defaults;
  }

}
