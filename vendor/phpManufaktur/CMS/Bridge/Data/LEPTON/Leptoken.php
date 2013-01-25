<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data\LEPTON;

class Leptoken {

  /**
   * Check wether a LEPTON Leptoken is active or not
   *
   * @return boolean
   */
  public static function isActive() {
    return isset($_GET['leptoken']);
  } // isActive()

  /**
   * Get the active Leptoken or an empty string
   *
   * @return string
   */
  public static function get() {
    return (self::isActive()) ? $_GET['leptoken'] : '';
  } // get()

  /**
   * If Leptoken isset, get a parameter string with the leptoken else an
   * empty string. Use the $prefix to insert a & or ? before.
   *
   * @param string $prefix
   * @return string
   */
  public static function getParameterString($prefix='') {
    return (self::isActive()) ? $prefix.'leptoken='.self::get() : '';
  } // getParameterString()

} // class Leptoken