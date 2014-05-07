<?php
/**
 * @file
 * A CSV output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Candidate;
use DrooPHP\ElectionInterface;
use DrooPHP\Method\MethodInterface;

class Csv extends FormatterBase {

  /**
   * @{inheritdoc}
   */
  public function getDefaults() {
    return ['delimiter' => ',', 'enclosure' => '"'];
  }

  /**
   * @{inheritdoc}
   */
  public function getOutput(MethodInterface $method, ElectionInterface $election) {

    $candidates = $election->candidates;
    $stages = $method->stages;

    $delimiter = $this->getConfig()->getOption('delimiter');
    $enclosure = $this->getConfig()->getOption('enclosure');

    $csv = fopen('php://temp', 'w');

    fputcsv($csv, ['Results: ' . trim($election->title)], $delimiter, $enclosure);

    fputcsv($csv, []);

    $elected_names = [];
    foreach ($candidates as $candidate) {
      if ($candidate->state === Candidate::STATE_ELECTED) {
        $elected_names[] = trim($candidate->name);
      }
    }

    $info = [];
    $info[] = ['Elected:', implode(', ', $elected_names)];
    $info[] = ['Number of candidates:', number_format($election->num_candidates)];
    $info[] = ['Vacancies:', number_format($election->num_seats)];
    $info[] = ['Valid ballots:', number_format($election->num_valid_ballots)];
    $info[] = ['Invalid ballots:', number_format($election->num_invalid_ballots)];
    $info[] = ['Quota:', number_format($method->quota)];
    $info[] = ['Stages:', number_format(count($stages))];
    $info[] = ['Count method:', $method->getName()];

    foreach ($info as $info_row) {
      fputcsv($csv, $info_row, $delimiter, $enclosure);
    }

    fputcsv($csv, []);

    $header = ['Candidates'];
    foreach (array_keys($stages) as $stage_id) {
      $header[] = sprintf('Stage %d', $stage_id);
    }
    fputcsv($csv, $header, $delimiter, $enclosure);

    foreach ($candidates as $candidate) {
      $row = [htmlspecialchars($candidate->name)];
      foreach ($stages as $stage) {
        $row[] = number_format($stage['votes'][$candidate->cid]);
      }
      fputcsv($csv, $row, $delimiter, $enclosure);
    }

    rewind($csv);
    $output = stream_get_contents($csv);
    fclose($csv);

    return $output;
  }

}
