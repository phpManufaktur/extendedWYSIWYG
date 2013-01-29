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

use phpManufaktur\CMS\Bridge\Control\boneClass;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygArchive;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygTeaser;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;
use phpManufaktur\extendedWYSIWYG\Data\pageSettings;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygExtension;
use phpManufaktur\extendedWYSIWYG\Data\archiveFile;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;

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


class boneJQueryControl extends boneClass {

  protected static $PAGE_ID = null;
  protected static $SECTION_ID = null;

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

  public function errorExit($error, $method, $line) {
    $this->setError($error, $method, $line);
    $data = array(
        'status' => 'ERROR',
        'message' => $this->formatError($error)
    );
    exit(json_encode($data));
  } // errorExit()

} // class boneJQueryControl













global $I18n;
global $logger;
global $dwoo;
global $tools;
global $cms;

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
    $data = array(
      'status' => 'ERROR',
      'message' => $error
      );
    exit(json_encode($data));
  }
  return $result;
} // formatError()

// check the essential parameters
if (!isset($_GET['section_id']) ||
    !isset($_GET['section_content']) ||
    !isset($_GET['page_id']) ||
    !isset($_GET['section_publish']) ||
    !isset($_GET['check_page_settings']) ||
    !isset($_GET['page_title']) ||
    !isset($_GET['page_description']) ||
    !isset($_GET['page_keywords']) ||
    !isset($_GET['check_teaser']) ||
    !isset($_GET['teaser_content']) ||
    !isset($_GET['teaser_publish']) ||
    !isset($_GET['teaser_id']) ||
    !isset($_GET['archive_id'])
    ) {
  /*
  $error = $I18n->translate('[ {{ file }} ] Missing essential parameters!', array('file' => basename(__FILE__)));
  $logger->addError(strip_tags($error));
  $error = formatError($error);
  $data = array(
      'status' => 'ERROR',
      'message' => $error
      );
  exit(json_encode($data));
  */
  $obj = new boneJQueryControl();
  $obj->errorExit($I18n->translate('[ {{ file }} ] Missing essential parameters!', array('file' => basename(__FILE__))), '', '');
}

// collect the messages of the script
$messages = '';

// get all parameters
$page_id = (int) $_GET['page_id'];
$section_id = (int) $_GET['section_id'];
$section_content = rawurldecode($_GET['section_content']);
$section_publish = (bool) $_GET['section_publish'];
$archive_id = (int) $_GET['archive_id'];

$check_page_settings = (bool) $_GET['check_page_settings'];
$page_title = rawurldecode($_GET['page_title']);
$page_description = rawurldecode($_GET['page_description']);
$page_keywords = rawurldecode($_GET['page_keywords']);

$check_teaser = $_GET['check_teaser'];
$teaser_content = rawurldecode($_GET['teaser_content']);
$teaser_publish = (bool) $_GET['teaser_publish'];
$teaser_id = (int) $_GET['teaser_id'];

// init the configuration
$configuration = new wysiwygConfiguration();
$cfgCreateArchiveFiles = $configuration->getValue('cfgCreateArchiveFiles');

// first step: update the page settings
if ($check_page_settings) {
  $settings = array(
      'page_title' => $tools->sanitizeText($page_title),
      'description' => $tools->sanitizeText($page_description),
      'keywords' => $tools->sanitizeText($page_keywords)
      );
  $page = new pageSettings();
  // save the page settings
  if (!$page->setSettings($page_id, $settings)) {
    $error = sprintf('[ %s ] %s', basename(__FILE__), $page->getError());
    $logger->addError($error);
    $data = array(
        'status' => 'ERROR',
        'message' => formatError($error)
    );
    exit(json_encode($data));
  }
  $message = $I18n->translate('<p>Page settings for the page with ID {{ page_id }} successfull updated.</p>',
      array('page_id' => $page_id));
  $logger->addInfo(strip_tags($message));
  $messages .= $message;
} // page settings

// second step: check if the page is used as blog
$teaser_result = array(
    'status' => 'NO_CHANGE',
    'teaser_id' => $teaser_id,
    'option' => '- no change -'
);
if ($check_teaser == 1) {
  $wysiwygTeaser = new wysiwygTeaser();
  if ($teaser_id > 0) {
    // get the old teaser by ID
    if (false === ($old_teaser = $wysiwygTeaser->select($teaser_id))) {
      $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygTeaser->getError());
      $logger->addError($error);
      $data = array(
          'status' => 'ERROR',
          'message' => formatError($error)
      );
      exit(json_encode($data));
    }
  }
  $old_hash = (isset($old_teaser['hash'])) ? $old_teaser['hash'] : '';
  $old_status = (isset($old_teaser['status'])) ? $old_teaser['status'] : 'ACTIVE';

  $new_hash = md5($teaser_content);
  $new_status = ($teaser_publish) ? 'ACTIVE' : 'UNPUBLISHED';

  if (($old_hash != $new_hash) || ($old_status != $new_status)) {
    // get the author
    $author = $cms->getUserDisplayName();
    // set the datetime
    $date = date('Y-m-d H:i:s');
    // set old teaser's to BACKUP and insert the new teaser with the given status
    if (!$wysiwygTeaser->insert($page_id, $teaser_content, $author, $date, $new_status, $teaser_id)) {
      $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygTeaser->getError());
      $logger->addError($error);
      $data = array(
          'status' => 'ERROR',
          'message' => formatError($error)
      );
      exit(json_encode($data));
    }
    $teaser_result = array(
        'status' => 'CHANGED',
        'teaser_id' => $teaser_id,
        'option' => sprintf('%s | %s', $date, $new_status)
        );
    $message = $I18n->translate('<p>The teaser for the page ID {{ page_id }} was success saved.</p>',
        array('page_id' => $page_id));
    $logger->addInfo(strip_tags($message));
    $messages .= $message;
  }
} // check teaser

// third step: save the content
$section_result = array(
    'status' => 'NO_CHANGE',
    'archive_id' => $archive_id,
    'option' => '- no change -'
);
$wysiwygArchive = new wysiwygArchive();
if (false === ($archive = $wysiwygArchive->select($archive_id))) {
  $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygArchive->getError());
  $logger->addError($error);
  $data = array(
      'status' => 'ERROR',
      'message' => formatError($error)
  );
  exit(json_encode($data));
}
$old_hash = (isset($archive['hash'])) ? $archive['hash'] : '';
$old_status = (isset($archive['status'])) ? $archive['status'] : '';

$new_hash = md5($section_content);
$new_status = ($section_publish) ? 'ACTIVE' : 'UNPUBLISHED';

if (($old_hash != $new_hash) || ($old_status != $new_status)) {
  // get the author
  $author = $cms->getUserDisplayName();
  // set the datetime
  $date = date('Y-m-d H:i:s');
  // set all old archive entries to status BACKUP and insert a new entry with the given STATUS
  if (false === ($wysiwygArchive->insert($page_id, $section_id, $section_content, $author, $new_status, $archive_id))) {
    $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygArchive->getError());
    $logger->addError($error);
    $data = array(
        'status' => 'ERROR',
        'message' => formatError($error)
    );
    exit(json_encode($data));
  }
  // ok - now we have to update the regular CMS WYSIWYG record!
  $wysiwygSection = new wysiwygSection();
  $wysiwyg_content = $section_content;
  // if the actual section content is UNPUBLISHED the wysiwyg section must be the last ACTIVE archive entry!
  if ($new_status != 'ACTIVE') {
    if (false === ($archive = $wysiwygArchive->selectLastActive($section_id))) {
      $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygArchive->getError());
      $logger->addError($error);
      $data = array(
          'status' => 'ERROR',
          'message' => formatError($error)
      );
      exit(json_encode($data));
    }
    if (count($archive) > 0)
      $wysiwyg_content = $archive['content'];
  }
  if (!$wysiwygSection->update($section_id, $wysiwyg_content)) {
    $error = sprintf('[ %s ] %s', basename(__FILE__), $wysiwygSection->getError());
    $logger->addError($error);
    $data = array(
        'status' => 'ERROR',
        'message' => formatError($error)
    );
    exit(json_encode($data));
  }

  $section_result = array(
      'status' => 'CHANGED',
      'archive_id' => $archive_id,
      'option' => sprintf('%s | %s', $date, $new_status)
  );
  $message = $I18n->translate('<p>The content for the SECTION ID {{ section_id }} was successfull saved.</p>',
      array('section_id' => $section_id));
  $logger->addInfo(strip_tags($message));
  $messages .= $message;

  // save the section to archive?
  if ($cfgCreateArchiveFiles) {
    $archiveFile = new archiveFile();
    $archive_file = '';
    if (!$archiveFile->save($archive_id, $archive_file)) {
      $error = sprintf('[ %s ] %s', basename(__FILE__), $archiveFile->getError());
      $logger->addError($error);
      $data = array(
          'status' => 'ERROR',
          'message' => formatError($error)
      );
      exit(json_encode($data));
    }
    $message = $I18n->translate('<p>Saved the section as archive file {{ archive_file }}.</p>',
        array('archive_file' => $archive_file));
    $logger->addInfo(strip_tags($message));
    $messages .= $message;
  }

} // save content


// quit the script and return all messages
if (empty($messages)) {
  $messages = $I18n->translate('<p>Nothing changed.</p>');
}
$messages = sprintf('<div class="wysiwyg_message">%s</div>', $messages);
$result = array(
    'status' => 'OK',
    'message' => $messages,
    'teaser' => $teaser_result,
    'section' => $section_result
    );
exit(json_encode($result));