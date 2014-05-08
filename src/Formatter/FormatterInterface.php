<?php
/**
 * @file
 * An interface for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\ResultInterface;

interface FormatterInterface {

  /**
   * @param ResultInterface $result
   *
   * @return string
   */
  public function getOutput(ResultInterface $result);

}
