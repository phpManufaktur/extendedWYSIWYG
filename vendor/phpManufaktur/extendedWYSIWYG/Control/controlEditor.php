<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\CMS\Bridge\Control\boneClass;

class controlEditor extends boneClass {

  protected static $USERNAME = null;

  /**
   * Constructor for class controlEditor
   *
   * @param unknown_type $username
   */
  public function __construct($username) {
    self::$USERNAME = $username;
  } // __construct()

  /**
   * Log a activity of the editor
   *
   * @param string $activity
   */
  public function activity($activity) {
    global $logger;

    $logger->addInfo(sprintf('[%s] %s', self::$USERNAME, $activity));
  } // activity()

} // class controlEditor
