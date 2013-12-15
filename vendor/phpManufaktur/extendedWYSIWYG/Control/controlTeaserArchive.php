<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file will be called by jQuery placed at the section editing page.
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygTeaser;
use phpManufaktur\extendedWYSIWYG\Data\pageSettings;


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
if (!isset($_GET['teaser_id'])) {
  $error = $I18n->translate('[ {{ file }} ] Missing essential parameters!', array('file' => basename(__FILE__)));
  $logger->addError(strip_tags($error));
  $error = formatError($error);
  $data = array(
      'status' => 'error',
      'message' => $error
      );
  exit(json_encode($data));
}

$teaser_id = (int) $_GET['teaser_id'];
$page_id = (int) $_GET['page_id'];

$teaser = new wysiwygTeaser();

if (false === ($teaser_record = $teaser->select($teaser_id))) {
  $error = formatError($teaser->getError());
  $data = array(
      'status' => 'error',
      'message' => $error
  );
  exit(json_encode($data));
}

$data = array(
    'status' => 'OK',
    'message'=> sprintf('<div class="wysiwyg_message">%s</div>', $I18n->translate(
        '<p>Successfull loaded the teaser archive with the ID {{ teaser_id }}.</p>', array('teaser_id' => $teaser_id))),
    'content' => $teaser_record['teaser_text'],
    'publish' => (int) ($teaser_record['status'] == 'ACTIVE')
);

exit(json_encode($data));
