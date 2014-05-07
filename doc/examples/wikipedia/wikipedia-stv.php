<?php
/**
 * @file Example vote count.
 */

// Load the DrooPHP library.
require __DIR__ . '/../../../library.php';

$file = __DIR__ . '/data/wikipedia-stv.blt';

$count = new DrooPHP\Count();
$count->getSource()->setOptions(['filename' => $file]);
print $count->run();
