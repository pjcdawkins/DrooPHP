<?php
/**
 * @file Example vote count.
 *
 * @see ./README.txt
 */

// Load the DrooPHP library.
require __DIR__ . '/../../../library.php';

$file = __DIR__ . '/data/SanFrancisco-Sheriff-2011.blt';

$options = [
  'filename' => $file,
  'allow_skipped' => TRUE,
  'allow_repeat' => TRUE,
  'allow_equal' => TRUE,
  'cache_dir' => __DIR__ . '/../cache',
];

$count = new DrooPHP\Count();
$count->getSource()->setOptions($options);
print $count->run();
