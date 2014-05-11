<?php
/**
 * @file Example vote count.
 */

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Load the DrooPHP library.
require __DIR__ . '/../../../library.php';

$file = __DIR__ . '/data/wikipedia-counting_stv.blt';

$count = new DrooPHP\Count(['method' => 'Ers97']);
$count->getSource()->setOptions(['filename' => $file, 'cache_enable' => FALSE]);
print $count->run();
