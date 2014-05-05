<?php
/**
 * @file
 * Interface for a source of election data.
 */

namespace DrooPHP\Source;

use DrooPHP\ElectionInterface;

interface SourceInterface {

  /**
   * Load the election.
   *
   * @return ElectionInterface
   */
  public function loadElection();

}
