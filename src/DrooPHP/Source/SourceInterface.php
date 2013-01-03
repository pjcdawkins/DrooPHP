<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Source;

use \DrooPHP\Config\ConfigInterface;

/**
 * Interface for a source of election data.
 */
interface SourceInterface
{

    /**
     * Load the election data.
     *
     * @return Election
     */
    public function loadElection();

}
