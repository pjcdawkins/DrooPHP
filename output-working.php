<?php
require 'library.php';

$file = 'doc/examples/wikipedia/data/wikipedia-stv.blt';

$count = new DrooPHP\Count($file);
$method = new DrooPHP\Method\Wikipedia($count);

$method->run();

$election = $count->election;


$headings = array('Stage');
foreach ($election->candidates as $candidate) {
    $headings[] = $candidate->name;
}

$rows = array();
foreach ($method->stages as $stage_name => $stage) {
    $row = array();
    $row[] = $stage_name;
    foreach ($election->candidates as $candidate) {
        $row[] = $stage['votes'][$candidate->cid];
    }
    $rows[] = $row;
}

$output = '<div class="droophp voting-results">';
$output .= '<table border="1" cellpadding="10" cellspacing="2">'
                . '<thead>';
$output .= '<tr>';
foreach ($headings as $heading) {
    $output .= '<th>';
    $output .= htmlspecialchars($heading);
    $output .= '</th>';
}
$output .= '</tr>';
foreach ($rows as $row) {
    $output .= '<tr>';
    foreach ($row as $cell) {
        $output .= '<td>' . htmlspecialchars($cell)    . '</td>';
    }
    $output .= '</tr>';
}
$output .= '</tbody>'
                . '</table>'
                . '</div>';

$output .= '<br />';

$output .= '<div class="droophp voting-results">';
$output .= '<table border="1" cellpadding="10" cellspacing="2">'
                . '<thead><tr><th>Candidate</th><th>Messages</th></tr>';
foreach ($election->candidates as $candidate) {
    $output .= '<tr>';
    $output .= '<th>' . htmlspecialchars($candidate->name) . '</th>';
    $output .= '<td>' . nl2br(htmlspecialchars(implode(PHP_EOL, $candidate->messages))) . '</td>';
    $output .= '</tr>';
}

$output .= '</div>';

echo $output;
