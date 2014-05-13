<?php
/**
 * @file
 * A CSV output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\CandidateInterface;
use DrooPHP\ResultInterface;

class Csv implements FormatterInterface, ConfigurableInterface {

  use ConfigurableTrait;

  /**
   * @{inheritdoc}
   */
  public function getDefaults() {
    return ['delimiter' => ',', 'enclosure' => '"'];
  }

  /**
   * @{inheritdoc}
   */
  public function getOutput(ResultInterface $result) {
    $election = $result->getElection();
    $candidates = $election->getCandidates();
    $stages = $result->getStages();

    $delimiter = $this->getConfig()->getOption('delimiter');
    $enclosure = $this->getConfig()->getOption('enclosure');

    $csv = fopen('php://temp', 'w');

    fputcsv($csv, ['Results: ' . trim($election->getTitle())], $delimiter, $enclosure);

    fputcsv($csv, []);

    $elected_names = [];
    foreach ($candidates as $candidate) {
      if ($candidate->getState() === CandidateInterface::STATE_ELECTED) {
        $elected_names[] = trim($candidate->getName());
      }
    }

    $info = [];
    $info[] = ['Elected:', implode(', ', $elected_names)];
    $info[] = ['Number of candidates:', number_format(count($candidates))];
    $info[] = ['Vacancies:', number_format($election->getNumSeats())];
    $info[] = [
      'Valid ballots:',
      number_format($election->getNumValidBallots())
    ];
    $info[] = [
      'Invalid ballots:',
      number_format($election->getNumInvalidBallots())
    ];
    $info[] = ['Quota:', number_format($result->getQuota())];
    $info[] = ['Stages:', number_format(count($stages))];
    $info[] = ['Count method:', $result->getMethodName()];

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
      $row = [htmlspecialchars($candidate->getName())];
      foreach ($stages as $stage) {
        $row[] = number_format($stage['votes'][$candidate->getId()]);
      }
      fputcsv($csv, $row, $delimiter, $enclosure);
    }

    rewind($csv);
    $output = stream_get_contents($csv);
    fclose($csv);

    return $output;
  }

}
