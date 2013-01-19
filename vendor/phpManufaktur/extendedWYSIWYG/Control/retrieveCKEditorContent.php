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

use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;
use phpManufaktur\extendedWYSIWYG\Data\pageSettings;

$path = __DIR__;
for ($i=0; $i < 10; $i++) {
  // try to find and load the bootstrap.php
  if (@file_exists($path.'/bootstrap.php')) {
    // enable access outside of the CMS!
    define('EXTERNAL_ACCESS', false);
    include $path.'/bootstrap.php';
    break;
  }
  $path = substr($path, 0, strrpos($path, '/'));
}

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
if (!isset($_REQUEST['section_id']) ||
    !isset($_REQUEST['section_content']) ||
    !isset($_REQUEST['page_id'])
    ) {
  $error = $I18n->translate('[ {{ file }} ] Missing essential parameters!', array('file' => basename(__FILE__)));
  $logger->addError(strip_tags($error));
  $error = formatError($error);
  exit($error);
}

// collect the messages of the script
$messages = '';

// save the WYSIWYG section
$page_id = (int) $_REQUEST['page_id'];
$section_id = (int) $_REQUEST['section_id'];
$section_content = rawurldecode($_REQUEST['section_content']);
$section = new wysiwygSection();
if (!$section->update($section_id, $section_content)) {
  $error = $I18n->translate('[ {{ file }} ] Error while updating the Section with the ID {{ section_id }}: {{ error }}',
      array('file' => basename(__FILE__), 'section_id' => $section_id, 'error' => $section->getError()));
  $logger->addError(strip_tags($error));
  $error = formatError($error);
  exit($error);
}
else {
  $message = $I18n->translate('<p>WYSIWYG Section {{ section_id }} successfull saved.</p>',
      array('section_id' => $section_id));
  $logger->addInfo(strip_tags($message));
  $messages .= $message;
}

if (isset($_REQUEST['check_page_settings']) && ($_REQUEST['check_page_settings'] == 1) &&
  isset($_REQUEST['page_title']) && isset($_REQUEST['page_description']) && isset($_REQUEST['page_keywords'])) {
  // save the page settings
  $fields = array(
      'page_title' => rawurldecode($_REQUEST['page_title']),
      'description' => rawurldecode($_REQUEST['page_description']),
      'keywords' => rawurldecode($_REQUEST['page_keywords'])
      );

  $page_settings = new pageSettings();
  if (!$page_settings->setSettings($page_id, $fields)) {
    $error = $I18n->translate('[ {{ file }} ] Error while updating the settings for the Page with the ID {{ page_id }}: {{ error }}',
        array('file' => basename(__FILE__), 'page_id' => $page_id, 'error' => $page_settings->getError()));
    $logger->addError(strip_tags($error));
    $error = formatError($error);
    exit($error);
  }
  $message = $I18n->translate('<p>Page settings for the page with ID {{ page_id }} successfull saved.</p>',
      array('page_id' => $page_id));
  $logger->addInfo(strip_tags($message));
  $messages .= $message;
}

// quit the script and return all messages
exit($messages);