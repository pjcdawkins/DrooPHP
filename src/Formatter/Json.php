<?php
/**
 * @file
 * A JSON output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\CandidateInterface;
use DrooPHP\ResultInterface;

class Json implements FormatterInterface {

  /**
   * @{inheritdoc}
   */
  public function getOutput(ResultInterface $result) {
    $election = $result->getElection();
    $candidates = $election->getCandidates();
    $stages = $result->getStages();
    $precision = $result->getPrecision();

    $output = ['title' => $election->getTitle()];

    $output['elected'] = [];
    foreach ($candidates as $candidate) {
      if ($candidate->getState() === CandidateInterface::STATE_ELECTED) {
        $output['elected'][] = $candidate->getName();
      }
    }

    $output['numCandidates'] = count($candidates);
    $output['numSeats'] = $election->getNumSeats();
    $output['numValidBallots'] = $election->getNumValidBallots();
    $output['numInvalidBallots'] = $election->getNumInvalidBallots();
    $output['quota'] = round($result->getQuota(), $precision);
    $output['numStages'] = count($stages);
    $output['method'] = $result->getMethodName();

    $output['results'] = [];

    foreach ($candidates as $candidate) {
      $row = [];
      foreach ($stages as $stage) {
        $row[] = round($stage['votes'][$candidate->getId()], $precision);
      }
      $output['results'][$candidate->getName()] = $row;
    }

    return json_encode($output);
  }

}
