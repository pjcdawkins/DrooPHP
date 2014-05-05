<?php
/**
 * @file
 * Interface for a source of election data.
 */

namespace DrooPHP\Source;

use DrooPHP\Count;
use DrooPHP\Election;

interface SourceInterface {

  /**
   * Constructor.
   */
  public function __construct(Count $count);

  /**
   * Load the election data.
   *
   * @return Election
   */
  public function loadElection();

}
