#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
  exit;
}

require_once __DIR__ . '/library.php';

$application = new DrooPHP\Cli\CountApplication();
$application->run();
