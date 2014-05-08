<?php
/**
 * @file
 * A plain-text output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\CandidateInterface;
use DrooPHP\ResultInterface;

class Text extends FormatterBase {

  /**
   * @{inheritdoc}
   */
  public function getOutput(ResultInterface $result) {
    $election = $result->getElection();
    $candidates = $election->getCandidates();
    $stages = $result->getStages();

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

    $title = sprintf('Results: %s', trim($election->getTitle()));

    $elected_names = [];
    foreach ($candidates as $candidate) {
      if ($candidate->getState() === CandidateInterface::STATE_ELECTED) {
        $elected_names[] = trim($candidate->getName());
      }
    }

    $output = "$title\n\n";

    $output .= sprintf("Elected: %s\n", implode(', ', $elected_names));
    $output .= sprintf("Number of candidates: %s\n", number_format(count($candidates)));
    $output .= sprintf("Vacancies: %s\n", number_format($election->getNumSeats()));
    $output .= sprintf("Valid ballots: %s\n", number_format($election->getNumValidBallots()));
    $output .= sprintf("Invalid ballots: %s\n", number_format($election->getNumInvalidBallots()));
    $output .= sprintf("Quota: %s\n", number_format($result->getQuota()));
    $output .= sprintf("Stages: %d\n", count($stages));
    $output .= sprintf("Count method: %d\n", $result->getMethodName());

    $output .= "\n$table";

    return $output;
  }

}
