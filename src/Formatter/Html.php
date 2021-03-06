<?php
/**
 * @file
 * An HTML output formatter.
 */

namespace DrooPHP\Formatter;

use DrooPHP\Config\ConfigurableInterface;
use DrooPHP\Config\ConfigurableTrait;
use DrooPHP\CandidateInterface;
use DrooPHP\ResultInterface;

class Html implements FormatterInterface, ConfigurableInterface {

  use ConfigurableTrait;

  /**
   * Overrides parent::getDefaults().
   */
  public function getDefaults() {
    return ['html_fragment' => FALSE];
  }

  /**
   * @{inheritdoc}
   */
  public function getOutput(ResultInterface $result) {
    $election = $result->getElection();
    $candidates = $election->getCandidates();
    $stages = $result->getStages();
    $precision = $result->getPrecision();

    $table_header = '<thead><tr><th rowspan="2">Candidates</th><th colspan="' . count($stages) . '">Number of votes</th></tr><tr>';
    foreach (array_keys($stages) as $stage_id) {
      $table_header .= '<th>' . sprintf('Stage %d', $stage_id) . '</th>';
    }
    $table_header .= '</tr></thead>';

    $table_rows = [];
    foreach ($candidates as $candidate) {
      $row = [];
      $row[] = htmlspecialchars($candidate->getName());
      foreach ($stages as $stage) {
        $cell = '<div class="droophp-votes">' . number_format($stage['votes'][$candidate->getId()], $precision) . '</div>';
        if (!empty($stage['changes'][$candidate->getId()])) {
          $cell .= '<ul class="droophp-changes"><li>' . implode(
              '</li><li>',
              array_map('htmlspecialchars', $stage['changes'][$candidate->getId()])
            ) . '</li></ul>';
        }
        $row[] = $cell;
      }
      $table_rows[] = $row;
    }

    $table_body = '<tbody>';
    foreach ($table_rows as $row) {
      $table_body .= '<tr><th class="droophp-candidate-name">' . array_shift($row) . '</th><td>' . implode('</td><td>', $row) . '</td></tr>';
    }
    $table_body .= '</tbody>';

    $table = '<table class="droophp-output">' . $table_header . $table_body . '</table>';

    $title = sprintf('Results: %s', htmlspecialchars($election->getTitle()));

    $elected_names = [];
    foreach ($candidates as $candidate) {
      if ($candidate->getState() === CandidateInterface::STATE_ELECTED) {
        $elected_names[] = trim($candidate->getName());
      }
    }

    $output = '<section class="droophp-results">';
    $output .= '<h1>' . $title . '</h1>';
    $output .= '<dl>';
    $output .= sprintf('<dt>Elected:</dt><dd>%s</dd>', htmlspecialchars(implode(', ', $elected_names)));
    $output .= sprintf('<dt>Number of candidates:</dt><dd>%s</dd>', number_format(count($candidates)));
    $output .= sprintf('<dt>Vacancies:</dt><dd>%s</dd>', number_format($election->getNumSeats()));
    $output .= sprintf('<dt>Valid ballots:</dt><dd>%s</dd>', number_format($election->getNumValidBallots()));
    $output .= sprintf('<dt>Invalid ballots:</dt><dd>%s</dd>', number_format($election->getNumInvalidBallots()));
    $output .= sprintf('<dt>Quota:</dt><dd>%s</dd>', number_format($result->getQuota(), $precision));
    $output .= sprintf('<dt>Stages:</dt><dd>%d</dd>', count($stages));
    $output .= sprintf('<dt>Count method:</dt><dd>%s</dd>', htmlspecialchars($result->getMethodName()));
    $output .= '</dl>';
    $output .= $table;
    $output .= '</section>';

    // Optionally, output as an HTML fragment (excluding DOCTYPE, etc).
    if ($this->getConfig()->getOption('html_fragment')) {
      return $output;
    }

    // Output a complete HTML5 page.
    $output = '<!DOCTYPE html>'
      . '<html lang="en" dir="ltr">'
      . '<head>'
      . '<meta charset="utf-8" />'
      . '<title>' . $title . '</title>'
      . '<style type="text/css">' . $this->getCss() . '</style>'
      . '</head>'
      . '<body>'
      . $output
      . '</body></html>';

    return $output;
  }

  /**
   * Get CSS for styling results.
   *
   * @return string
   */
  public function getCss() {
    return file_get_contents($this->getCssFile());
  }

  /**
   * Get the complete filename for the results CSS.
   */
  protected function getCssFile() {
    return __DIR__ . '/styles.css';
  }

}
