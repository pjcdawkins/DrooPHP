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
   *
   * @return string
   */
  public function formatTable(array $rows) {
    $table = '';
    $columns = 100;
    // If this is for command-line output, attempt to find the terminal width.
    if (php_sapi_name() == 'cli') {
      $columns = exec('tput cols') ? : 80;
    }
    $max_width = floor($columns / count($rows[0]));
    $min_width = 6;
    $widths = [];
    foreach ($rows as $row) {
      foreach ($row as $col => $cell) {
        $length = strlen($cell) + 1;
        if ($length <= $min_width) {
          continue;
        }
        if ($length > $max_width) {
          $length = $max_width;
        }
        if (!isset($widths[$col]) || $length > $widths[$col]) {
          $widths[$col] = $length;
        }
      }
    }
    foreach ($rows as $row) {
      $cell_no = 1;
      foreach ($row as $col => $cell) {
        if ($cell_no++ > 1) {
          $table .= "\t";
        }
        $length = isset($widths[$col]) ? $widths[$col] : $min_width;
        $table .= utf8_encode(sprintf('%' . $length . 's', utf8_decode($cell)));
      }
      $table .= "\n";
    }
    return $table;
  }

}
