<?php
/**
 * @file
 * A plain-text output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\CandidateInterface;
use DrooPHP\ResultInterface;

class Text implements FormatterInterface, ConfigurableInterface {

  use ConfigurableTrait;

  /**
   * @{inheritdoc}
   */
  public function getOutput(ResultInterface $result) {
    $election = $result->getElection();
    $candidates = $election->getCandidates();
    $stages = $result->getStages();
    $precision = $result->getPrecision();

    $table_rows = [];
    $header = ['Candidates'];
    foreach (array_keys($stages) as $stage_id) {
      $header[] = sprintf('Stage %d', $stage_id);
    }
    $table_rows[] = $header;

    foreach ($candidates as $candidate) {
      $row = [];
      $row[] = $candidate->getName();
      foreach ($stages as $stage) {
        $row[] = number_format($stage['votes'][$candidate->getId()], $precision);
      }
      $table_rows[] = $row;
    }

    $totals = ['Total vote'];
    foreach ($stages as $stage) {
      $totals[] = number_format($stage['total'], $precision);
    }
    $table_rows[] = $totals;

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
    $output .= sprintf("Count method: %s\n", $result->getMethodName());

    $output .= "\n";

    $output .= $this->formatTable($table_rows);

    return $output;
  }

  /**
   * Output a plain-text table.
   *
   * @param array $rows
   * @param int $column_width
   * @param string $align
   * @param bool $utf8
   *
   * @return string
   */
  public function formatTable(array $rows, $column_width = 15, $align = 'right', $utf8 = TRUE) {
    $table = '';
    $format = '%' . $column_width . 's';
    if ($align == 'left') {
      $format = '%-' . $column_width . 's';
    }
    foreach ($rows as $row) {
      $cell_no = 1;
      foreach ($row as $cell) {
        if ($cell_no++ > 1) {
          $table .= "\t";
        }
        if ($utf8) {
          $table .= utf8_encode(sprintf($format, utf8_decode($cell)));
        }
        else {
          $table .= sprintf($format, $cell);
        }
      }
      $table .= "\n";
    }
    return $table;
  }

}
