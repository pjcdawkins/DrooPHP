<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Formatter;

use DrooPHP\Count;

interface FormatterInterface {

  /**
   * Constructor.
   */
  public function __construct(Count $count);

  /**
   * @return string
   */
  public function getOutput();

}
