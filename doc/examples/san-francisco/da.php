<?php
require '../../../library.php';

$file = 'data/SanFrancisco-DA-2011.blt';

$options = array(
  'allow_skipped' => 1,
  'allow_repeat' => 1,
  'allow_equal' => 1,
);

$count = new DrooPHP_Count($file, $options);

$method = new DrooPHP_Method_Wikipedia($count);
$method->run();

echo '<pre>' . htmlspecialchars(print_r($method, true)) . '</pre>';
