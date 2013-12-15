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

use phpManufaktur\extendedWYSIWYG\Data\wysiwygExtension;

define('EXTERNAL_ACCESS', false);
include realpath(__DIR__.'/../../../../bootstrap.php');

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

global $I18n;
global $logger;
global $dwoo;

$error_template = CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/error.dwoo';

/**
 * Format an error message
 *
 * @param string $error_message
 * @return Ambigous <string, mixed>
 */
function formatError($error_message) {
  global $dwoo;
  global $error_template;
  global $logger;
  global $I18n;

  try {
    $data = array('content' => $error_message);
    $result = $dwoo->get($error_template, $data);
  } catch (\Dwoo_Exception $e) {
    $error = $I18n->translate('[ {{ file }} ] Error executing the template <b>{{ template }}</b>: {{ error }}',
        array('template' => basename($error_template), 'error' => $e->getMessage(), 'file' => basename(__FILE__)));
    $logger->addError(strip_tags($error));
    // important: exit with the ORIGIN error message, not with the template error!
    exit($error_message);
  }
  return $result;
} // formatError()

// check the essential parameters

if (!isset($_GET['page_id']) || !isset($_GET['section_id']) || !isset($_GET['options'])) {
  $error = $I18n->translate('[ {{ file }} ] Missing essential parameters!', array('file' => basename(__FILE__)));
  $logger->addError(strip_tags($error));
  $error = formatError($error);
  exit($error);
}

$page_id = (int) $_GET['page_id'];
$section_id = (int) $_GET['section_id'];
$options = explode(',', $_GET['options']);

$extension = new wysiwygExtension();

if (!$extension->existsOptions($page_id, $section_id)) {
  // insert a new record
  if (!$extension->insertOptions($page_id, $section_id, $options)) {
    $error = formatError($extension->getError());
    exit($error);
  }
}
else {
  // update the options
  if (!$extension->updateOptions($page_id, $section_id, $options)) {
    $error = formatError($extension->getError());
    exit($error);
  }
}

exit('OK');
