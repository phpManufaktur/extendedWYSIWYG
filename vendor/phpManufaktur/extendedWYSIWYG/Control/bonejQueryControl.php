<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\CMS\Bridge\Control\boneClass;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

/**
 * Base class for controls executed by jQuery
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 */
class bonejQueryControl extends boneClass {

  protected static $PAGE_ID = null;
  protected static $SECTION_ID = null;

  /**
   * Format an error message with the assigned template
   *
   * @param string $error_message
   * @return Ambigous <string, mixed>
   */
  protected function formatError($error_message) {
    global $dwoo;
    global $error_template;
    global $logger;
    global $I18n;

    $error_template = CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/error.dwoo';

    try {
      $data = array(
          'content' => $error_message
          );
      $result = $dwoo->get($error_template, $data);
    } catch (\Dwoo_Exception $e) {
      $error = $I18n->translate('[ {{ file }} ] Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($error_template), 'error' => $e->getMessage(), 'file' => basename(__FILE__)));
      $logger->addError(strip_tags($error));
      // important: exit with the ORIGIN error message, not with the template error!
      $data = array(
          'status' => 'ERROR',
          'message' => $error
      );
      exit(json_encode($data));
    }
    return $result;
  } // formatError()

  /**
   * Set the error with the regular setError() method, create a data record for
   * the jQuery response, use json encode and exit the script
   *
   * @param string $error
   * @param string $method
   * @param string $line
   */
  protected function errorExit($error, $method, $line) {
    $this->setError($error, $method, $line);
    $data = array(
        'status' => 'ERROR',
        'message' => $this->formatError($error)
    );
    exit(json_encode($data));
  } // errorExit()

} // class bonejQueryControl
