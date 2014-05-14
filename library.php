<?php
/**
 * @file Include this file to load the DrooPHP library.
 */

// The library requires Composer (see getcomposer.org). Composer creates an
// autoload file; check that it exists.
$local_autoload = __DIR__ . '/vendor/autoload.php';
$global_autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!is_readable($local_autoload) && !is_readable($global_autoload)) {
  throw new Exception('Autoload file not available (have you run Composer?)');
}

require is_readable($local_autoload) ? $local_autoload : $global_autoload;
