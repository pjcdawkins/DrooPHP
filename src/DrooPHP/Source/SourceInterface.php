<?php
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
