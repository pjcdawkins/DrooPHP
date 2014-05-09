<?php
/**
 * @file
 * A base class for an output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\Config\ConfigurableInterface;

/**
 * A base class for an output formatter.
 */
abstract class FormatterBase implements FormatterInterface, ConfigurableInterface {

  use ConfigurableTrait;

}
