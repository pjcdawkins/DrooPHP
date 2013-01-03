<?php
/**
 * @file Example vote count.
 *
 * @see ./README.txt
 *
 * @package DrooPHP
 */

// Display errors (just for testing).
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Load the DrooPHP library.
require '../../../library.php';

$file = 'data/SanFrancisco-Mayor-2011.blt';

$options = array(
    'filename' => $file,
    'allow_skipped' => TRUE,
    'allow_repeat' => TRUE,
    'allow_equal' => TRUE,
);

$count = new DrooPHP\Count($options);
print $count->run();
