<?php
/**
 * @file
 * A plain-text output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\CandidateInterface;
use DrooPHP\Method\MethodInterface;

class Text extends FormatterBase {

  /**
   * @{inheritdoc}
   */
  public function getOutput(MethodInterface $method) {
    $election = $method->getElection();
    $candidates = $election->getCandidates();
    $stages = $method->getStages();

    $table_header = "Candidates";
    foreach (array_keys($stages) as $stage_id) {
      $table_header .= "\t" . sprintf('Stage %d', $stage_id);
    }
    $table_header .= "\n";

    $table_rows = [];
    foreach ($candidates as $candidate) {
      $row = [];
      $row[] = htmlspecialchars($candidate->getName());
      foreach ($stages as $stage) {
        $row[] = number_format($stage['votes'][$candidate->getId()]);
      }
      $table_rows[] = $row;
    }

    $table_body = '';
    foreach ($table_rows as $row) {
      $table_body .= array_shift($row) . "\t" . implode("\t", $row) . "\n";
    }

    $table = $table_header . $table_body;

    $title = sprintf('Results: %s', trim($election->title));

    $elected_names = [];
    foreach ($candidates as $candidate) {
      if ($candidate->getState() === CandidateInterface::STATE_ELECTED) {
        $elected_names[] = trim($candidate->getName());
      }
    }

    $output = "$title\n\n";

    $output .= sprintf("Elected: %s\n", implode(', ', $elected_names));
    $output .= sprintf("Number of candidates: %s\n", number_format($election->num_candidates));
    $output .= sprintf("Vacancies: %s\n", number_format($election->num_seats));
    $output .= sprintf("Valid ballots: %s\n", number_format($election->num_valid_ballots));
    $output .= sprintf("Invalid ballots: %s\n", number_format($election->num_invalid_ballots));
    $output .= sprintf("Quota: %s\n", number_format($method->getQuota()));
    $output .= sprintf("Stages: %d\n", count($stages));
    $output .= sprintf("Count method: %d\n", $method->getName());

    $output .= "\n$table";

    return $output;
  }

}
