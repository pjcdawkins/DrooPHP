<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Formatter;

use DrooPHP\Candidate;

/**
 * A plain-text output format.
 */
class Text extends FormatterBase {

  /**
   * @{inheritdoc}
   */
  public function getOutput() {
    $election = $this->count->getElection();
    $method = $this->count->getMethod();

    $candidates = $election->candidates;
    $stages = $method->stages;

    $table_header = "Candidates";
    foreach (array_keys($stages) as $stage_id) {
      $table_header .= "\t" . sprintf('Stage %d', $stage_id);
    }
    $table_header .= "\n";

    $table_rows = array();
    foreach ($candidates as $candidate) {
      $row = array();
      $row[] = htmlspecialchars($candidate->name);
      foreach ($stages as $stage) {
        $row[] = number_format($stage['votes'][$candidate->cid]);
      }
      $table_rows[] = $row;
    }

    $table_body = '';
    foreach ($table_rows as $row) {
      $table_body .= array_shift($row) . "\t" . implode("\t", $row) . "\n";
    }

    $table = $table_header . $table_body;

    $title = sprintf('Results: %s', htmlspecialchars($election->title));

    $elected_names = array();
    foreach ($candidates as $candidate) {
      if ($candidate->state === Candidate::STATE_ELECTED) {
        $elected_names[] = $candidate->name;
      }
    }

    $output = "$title\n\n";

    $output .= sprintf("Elected: %s\n", htmlspecialchars(implode(', ', $elected_names)));
    $output .= sprintf("Number of candidates: %s\n", number_format($election->num_candidates));
    $output .= sprintf("Vacancies: %s\n", number_format($election->num_seats));
    $output .= sprintf("Valid ballots: %s\n", number_format($election->num_valid_ballots));
    $output .= sprintf("Invalid ballots: %s\n", number_format($election->num_invalid_ballots));
    $output .= sprintf("Quota: %s\n", number_format($method->quota));
    $output .= sprintf("Stages: %d\n", count($stages));

    $output .= "\n$table";

    return $output;
  }

}
