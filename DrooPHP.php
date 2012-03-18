<?php
/**
 * @file
 *   DrooPHP class.
 * @package
 *   DrooPHP
 */
/**
 * @class
 *   DrooPHP
 *   Main class for a DrooPHP count.
 */
class DrooPHP {

  /**
   * Static method initiating the library.
   *
   * @return void
   */
  public static function init() {
    spl_autoload_register(array('DrooPHP', 'autoload'));
  }

  /**
   * The autoloader: registered in self::init().
   *
   * @param string $class_name
   * @return void
   */
  public static function autoload($class_name) {
    $filename = dirname(__FILE__) . '/' . str_replace('_', '/', $class_name) . '.php';
    include($filename);
  }

}