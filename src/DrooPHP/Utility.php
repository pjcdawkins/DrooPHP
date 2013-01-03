<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

/**
 * Main class for an election count.
 */
abstract class Utility
{

    /**
     * Test a configuration option value against the name of an interface.
     *
     * @param mixed $value           The value of the option (object or string).
     * @param string $interface_name The absolute name of an interface that the
     *                               class or object must implement.
     * @param string $namespace      A namespace for the class name (optional).
     *
     * @return mixed
     *     The option value (namespaced if relevant) or FALSE on failure.
     */
    public static function validateConfigOption($value, $interface_name, $namespace = NULL)
    {
        if (is_object($value) && $value instanceof $interface_name) {
            return $value;
        }
        if ($value && is_string($value)) {
            // Standardise the case.
            // This is just so filenames are more likely to match for
            // case-sensitive autoloading. The function class_exists() is
            // otherwise case insensitive.
            $value = ucwords(strtolower($value));
            // Add the namespace if appropriate.
            if ($namespace) {
                $namespaced = $namespace . '\\' . $value;
                if (class_exists($namespaced)) {
                    $value = $namespaced;
                }
            }
            // Test against the absolute interface name.
            if (class_exists($value) && in_array($interface_name, class_implements($value))) {
                return $value;
            }
        }
        return FALSE;
    }

}
