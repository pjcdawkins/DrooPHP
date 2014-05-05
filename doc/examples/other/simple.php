<?php
/**
 * @file Example vote count.
 */

// Display errors (just for testing).
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Load the DrooPHP library.
require '../../../library.php';

$file = 'data/simple.blt';
$source = new DrooPHP\Source\File(array('filename' => $file));

$count = new DrooPHP\Count(NULL, $source);
print $count->run();
