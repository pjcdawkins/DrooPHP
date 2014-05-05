<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Source;

use DrooPHP\Count;
use DrooPHP\Election;

/**
 * Interface for a source of election data.
 */
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
