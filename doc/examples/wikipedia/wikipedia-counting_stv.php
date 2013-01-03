<?php
/**
 * @file Example vote count.
 *
 * @see README.txt
 *
 * @package DrooPHP
 */

require '../../../library.php';

$file = 'data/wikipedia-counting_stv.blt';

$options = array(
    'filename' => $file,
);

$count = new DrooPHP\Count(
    new DrooPHP\Source\File(),
    new DrooPHP\Method\Wikipedia(),
    $options
);

$output = $count->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($output);
