<?php
/**
 * @file
 *   Main library file for DrooPHP. You'll need to include this file to load the library.
 * @package
 *   DrooPHP
 */

// Register the autoloader.
spl_autoload_register('droophp_autoloader');

/**
 * Autoloader callback function.
 *
 * @param string $class_name
 *
 * @return void
 */
function droophp_autoloader($class_name) {
  $filename = dirname(__FILE__) . '/' . str_replace('_', '/', $class_name) . '.php';
  include($filename);
}
