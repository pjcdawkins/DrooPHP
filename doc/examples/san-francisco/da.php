<?php
/**
 * @file Example vote count.
 *
 * @see README.txt
 *
 * @package DrooPHP
 */

require '../../../library.php';

$file = 'data/SanFrancisco-DA-2011.blt';

$options = array(
    'filename' => $file,
    'allow_skipped' => TRUE,
    'allow_repeat' => TRUE,
    'allow_equal' => TRUE,
);

$count = new DrooPHP\Count(
    new DrooPHP\Source\File(),
    new DrooPHP\Method\Wikipedia(),
    $options
);

$output = $count->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($output);
