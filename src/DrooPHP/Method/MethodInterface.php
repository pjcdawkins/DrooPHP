<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

/**
 * Interface for a vote counting method.
 */
interface MethodInterface
{

    /**
     * Run the count.
     */
    public function run();

}
