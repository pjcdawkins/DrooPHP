<?php
/**
 * @file
 * Include this file to load the DrooPHP library.
 */

// Include the autoloader, and register DrooPHP.
require 'lib/SplClassLoader.php';
$classLoader = new SplClassLoader('DrooPHP', dirname(__FILE__) . '/lib');
$classLoader->register(true);
