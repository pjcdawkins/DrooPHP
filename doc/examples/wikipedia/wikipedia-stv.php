<?php
require '../../../library.php';

$file = 'data/wikipedia-stv.blt';

$count = new DrooPHP_Count($file);
$method = new DrooPHP_Method_Wikipedia($count);

$method->run();

echo '<pre>' . htmlspecialchars(print_r($method, true)) . '</pre>';
