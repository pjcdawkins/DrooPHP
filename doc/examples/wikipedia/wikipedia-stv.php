<?php
/**
 * @file Example vote count.
 */

// Display errors (just for testing).
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Load the DrooPHP library.
require '../../../library.php';

$file = 'data/wikipedia-stv.blt';

$count = new DrooPHP\Count(array('filename' => $file));
print $count->run();
