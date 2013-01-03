<?php
/**
 * @file Include this file to load the DrooPHP library.
 *
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

// The library requires Composer (see getcomposer.org). Composer creates an
// autoload file; check that it exists.
if (!is_readable(__DIR__ . '/vendor/autoload.php')) {
  throw new Exception('Autoload file not available (have you run Composer?)');
}

require 'vendor/autoload.php';
