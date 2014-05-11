<?php
/**
 * @file Example vote count.
 */

// Load the DrooPHP library.
require __DIR__ . '/../../../library.php';

$file = __DIR__ . '/data/simple.blt';

$source = new DrooPHP\Source\File(['filename' => $file]);
$count = new DrooPHP\Count(['source' => $source]);
print $count->run();
