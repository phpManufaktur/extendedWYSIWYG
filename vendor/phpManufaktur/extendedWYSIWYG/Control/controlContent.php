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

use phpManufaktur\CMS\Bridge\Data\LEPTON\Users;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygMessages;
use phpManufaktur\CMS\Bridge\Data\LEPTON\Pages;
use phpManufaktur\extendedWYSIWYG\Data\editorDepartment;
use phpManufaktur\CMS\Bridge\Control\swiftMailer;
use phpManufaktur\extendedWYSIWYG\Data\editorTeam;
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

/**
 * Section content control - called by jQuery modifySection.js
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 */
class controlContent extends bonejQueryControl {

  const ANCHOR = 'wysiwyg_';

  protected static $SECTION_CONTENT = null;
  protected static $SECTION_PUBLISH = null;
  protected static $CHECK_PAGE_SETTINGS = null;
  protected static $PAGE_TITLE = null;
  protected static $PAGE_DESCRIPTION = null;
  protected static $PAGE_KEYWORDS = null;
  protected static $CHECK_TEASER = null;
  protected static $TEASER_CONTENT = null;
  protected static $TEASER_PUBLISH = null;
  protected static $TEASER_ID = null;
  protected static $ARCHIVE_ID = null;
  protected static $CREATE_ARCHIVE_FILES = null;
  protected static $TEASER_RESULT = null;
  protected static $SECTION_RESULT = null;
  protected static $WYSIWYG_CONTENT = null;
  protected static $EDITOR_NAME = null;
  protected static $EDITORIAL_SYSTEM_IS_ACTIVE = null;
  protected static $TEMPLATE_PATH = null;
  protected static $MODIFY_PAGE_URL = null;
  protected static $EMAIL_TEXT = null;
  protected static $EDITOR_ACTION = null;
  protected static $EDITOR_RESPONSE = null;
  protected static $EMAIL_SEND = null;

  public function __construct() {
    // init the configuration
    $configuration = new wysiwygConfiguration();
    self::$CREATE_ARCHIVE_FILES = $configuration->getValue('cfgCreateArchiveFiles');
    self::$EDITORIAL_SYSTEM_IS_ACTIVE = $configuration->getValue('cfgUseEditorialDepartment');
    // set the template path
    self::$TEMPLATE_PATH = CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/emails/';
    self::$MODIFY_PAGE_URL = CMS_ADMIN_URL.'/pages/modify.php';
  } // __construct()

  /**
   * Get the template, set the data and return the compiled
   *
   * @param string $template the name of the template
   * @param array $template_data
   * @param boolean $trigger_error raise a trigger error on problems
   * @return boolean|Ambigous <string, mixed>
   */
  protected function getTemplate($template, $template_data) {
    global $dwoo;
    global $I18n;

    // check if a custom template exists ...
    $load_template = (file_exists(self::$TEMPLATE_PATH.'custom.'.$template)) ? self::TEMPLATE_PATH.'custom.'.$template : self::$TEMPLATE_PATH.$template;
    try {
      $result = $dwoo->get($load_template, $template_data);
    }
    catch (\Dwoo_Exception $e) {
      $this->errorExit($I18n->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Check the needed requests and set the statics for the class
   *
   * @return boolean;
   */
  protected function checkRequests() {
    global $I18n;
    global $cms;

    if (!isset($_REQUEST['section_id']) ||
        !isset($_REQUEST['section_content']) ||
        !isset($_REQUEST['page_id']) ||
        !isset($_REQUEST['section_publish']) ||
        !isset($_REQUEST['check_page_settings']) ||
        !isset($_REQUEST['page_title']) ||
        !isset($_REQUEST['page_description']) ||
        !isset($_REQUEST['page_keywords']) ||
        !isset($_REQUEST['check_teaser']) ||
        !isset($_REQUEST['teaser_content']) ||
        !isset($_REQUEST['teaser_publish']) ||
        !isset($_REQUEST['teaser_id']) ||
        !isset($_REQUEST['archive_id']) ||
        !isset($_REQUEST['editor_name']) ||
        !isset($_REQUEST['email_text']) ||
        !isset($_REQUEST['editor_action']) ||
        !isset($_REQUEST['editor_response']) ||
        !isset($_REQUEST['email_send'])
    ) {
      $this->errorExit($I18n->translate('[ {{ file }} ] Missing essential parameters!',
          array('file' => basename(__FILE__))), __METHOD__, __LINE__);
      return false;
    }
    self::$PAGE_ID = (int) $_REQUEST['page_id'];
    self::$SECTION_ID = (int) $_REQUEST['section_id'];
    self::$SECTION_CONTENT = rawurldecode($_REQUEST['section_content']);
    self::$SECTION_PUBLISH = (bool) $_REQUEST['section_publish'];
    self::$ARCHIVE_ID = (int) $_REQUEST['archive_id'];

    self::$CHECK_PAGE_SETTINGS = (bool) $_REQUEST['check_page_settings'];
    self::$PAGE_TITLE = rawurldecode($_REQUEST['page_title']);
    self::$PAGE_DESCRIPTION = rawurldecode($_REQUEST['page_description']);
    self::$PAGE_KEYWORDS = rawurldecode($_REQUEST['page_keywords']);

    self::$CHECK_TEASER = $_REQUEST['check_teaser'];
    self::$TEASER_CONTENT = rawurldecode($_REQUEST['teaser_content']);
    self::$TEASER_PUBLISH = (bool) $_REQUEST['teaser_publish'];
    self::$TEASER_ID = (int) $_REQUEST['teaser_id'];

    self::$EDITOR_NAME = !empty($_REQUEST['editor_name']) ? trim($_REQUEST['editor_name']) : $cms->getUserLoginName();
    self::$EMAIL_TEXT = rawurldecode($_REQUEST['email_text']);
    self::$EDITOR_ACTION = $_REQUEST['editor_action'];
    self::$EDITOR_RESPONSE = $_REQUEST['editor_response'];
    self::$EMAIL_SEND = $_REQUEST['email_send'];

    return true;
  } // checkRequests()

  /**
   * Update the page settings
   *
   * @return boolean
   */
  protected function checkPageSettings() {
    global $I18n;

    if (self::$CHECK_PAGE_SETTINGS) {
      $settings = array(
          'page_title' => self::$PAGE_TITLE,
          'description' => self::$PAGE_DESCRIPTION,
          'keywords' => self::$PAGE_KEYWORDS
      );
      $pageSettings = new pageSettings();
      // save the page settings
      if (!$pageSettings->updateSettings(self::$PAGE_ID, $settings)) {
        $this->errorExit($pageSettings->getError(), __METHOD__, __LINE__);
        return false;
      }
      $this->setMessage($I18n->translate('<p>Page settings for the page with ID {{ page_id }} successfull updated.</p>',
          array('page_id' => self::$PAGE_ID)), __METHOD__, __LINE__);
    }
    return true;
  } // checkPageSettings()

  /**
   * Check if the teaser has changed
   *
   * @return boolean
   */
  protected function checkTeaser() {
    global $I18n;
    global $cms;

    // set the default result
    self::$TEASER_RESULT = array(
        'status' => 'NO_CHANGE',
        'teaser_id' => self::$TEASER_ID,
        'option' => '- no change -'
    );
    if (self::$CHECK_TEASER == 1) {
      $wysiwygTeaser = new wysiwygTeaser();
      if (self::$TEASER_ID > 0) {
        // get the old teaser by ID
        if (false === ($old_teaser = $wysiwygTeaser->select(self::$TEASER_ID))) {
          $this->errorExit($wysiwygTeaser->getError(), __METHOD__, __LINE__);
          return false;
        }
      }
      $old_hash = (isset($old_teaser['hash'])) ? $old_teaser['hash'] : '';
      $old_status = (isset($old_teaser['status'])) ? $old_teaser['status'] : 'ACTIVE';

      $new_hash = md5(self::$TEASER_CONTENT);
      $new_status = (self::$TEASER_PUBLISH) ? 'ACTIVE' : 'UNPUBLISHED';

      if (($old_hash != $new_hash) || ($old_status != $new_status)) {
        // get the author
        $author = $cms->getUserDisplayName();
        // set the datetime
        $date = date('Y-m-d H:i:s');
        // set old teaser's to BACKUP and insert the new teaser with the given status
        if (!$wysiwygTeaser->insert(self::$PAGE_ID, self::$TEASER_CONTENT, $author, $date, $new_status, self::$TEASER_ID)) {
          $this->errorExit($wysiwygTeaser->getError(), __METHOD__, __LINE__);
          return false;
        }
        self::$TEASER_RESULT = array(
            'status' => 'CHANGED',
            'teaser_id' => self::$TEASER_ID,
            'option' => sprintf('%s | %s', $date, $new_status)
            );
        $this->setMessage($I18n->translate('<p>The teaser for the page ID {{ page_id }} was successfull saved.</p>',
            array('page_id' => self::$PAGE_ID)), __METHOD__, __LINE__);
      }
    }
    return true;
  } // checkTeaser()

  /**
   * Check if the WYSIWYG section has changed, create archive and set the state flag
   *
   * @return boolean
   */
  protected function checkSection() {
    global $I18n;
    global $cms;

    // set default response
    self::$SECTION_RESULT = array(
        'status' => 'NO_CHANGE',
        'archive_id' => self::$ARCHIVE_ID,
        'option' => '- no change -'
        );
    // init the archive
    $wysiwygArchive = new wysiwygArchive();
    if (false === ($archive = $wysiwygArchive->select(self::$ARCHIVE_ID))) {
      $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }
    $old_hash = (isset($archive['hash'])) ? $archive['hash'] : '';
    $old_status = (isset($archive['status'])) ? $archive['status'] : '';

    $new_hash = md5(self::$SECTION_CONTENT);
    $new_status = (self::$SECTION_PUBLISH) ? 'ACTIVE' : 'UNPUBLISHED';

    if (($old_hash != $new_hash) || ($old_status != $new_status)) {
      // get the author
      $author = $cms->getUserDisplayName();
      // set the datetime
      $date = date('Y-m-d H:i:s');
      // set all old archive entries to status BACKUP and insert a new entry with the given STATUS
      if (false === ($wysiwygArchive->insert(self::$PAGE_ID, self::$SECTION_ID, self::$SECTION_CONTENT, $author, $new_status, self::$ARCHIVE_ID))) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }
      // ok - now we have to update the regular CMS WYSIWYG record!
      $wysiwygSection = new wysiwygSection();
      self::$WYSIWYG_CONTENT = self::$SECTION_CONTENT;
      // if the actual section content is UNPUBLISHED the wysiwyg section must be the last ACTIVE archive entry!
      if ($new_status != 'ACTIVE') {
        if (false === ($archive = $wysiwygArchive->selectLastActive(self::$SECTION_ID))) {
          $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
          return false;
        }
        if (count($archive) > 0)
          self::$WYSIWYG_CONTENT = $archive['content'];
      }
      if (!$wysiwygSection->update(self::$SECTION_ID, self::$WYSIWYG_CONTENT)) {
        $this->errorExit($wysiwygSection->getError(), __METHOD__, __LINE__);
        return false;
      }

      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'CHANGED',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', $date, $new_status)
      );
      $this->setMessage($I18n->translate('<p>The content for the SECTION ID {{ section_id }} was successfull saved.</p>',
          array('section_id' => self::$SECTION_ID)), __METHOD__, __LINE__);

      // save the section to archive?
      if (self::$CREATE_ARCHIVE_FILES) {
        $archiveFile = new archiveFile();
        $archive_file = '';
        if (!$archiveFile->save(self::$ARCHIVE_ID, $archive_file)) {
          $this->errorExit($archiveFile->getError(), __METHOD__, __LINE__);
          return false;
        }
        $this->setMessage($I18n->translate('<p>Saved the section as archive file {{ archive_file }}.</p>',
            array('archive_file' => $archive_file)), __METHOD__, __LINE__);
      }
    }
    return true;
  } // checkSection()

  /**
   * Check the section if the editorial system is active!
   *
   * @return boolean
   */
  protected function checkEditorialSection() {
    global $I18n;
    global $cms;

    // set default response
    self::$SECTION_RESULT = array(
        'status' => 'NO_CHANGE',
        'archive_id' => self::$ARCHIVE_ID,
        'option' => '- no change -'
    );

    // save as draft - init the archive
    $wysiwygArchive = new wysiwygArchive();
    if (false === ($archive = $wysiwygArchive->select(self::$ARCHIVE_ID))) {
      $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }
    $old_hash = (isset($archive['hash'])) ? $archive['hash'] : '';
    $old_status = (isset($archive['status'])) ? $archive['status'] : '';

    $new_hash = md5(self::$SECTION_CONTENT);
    $new_status = 'DRAFT';

    if (($old_hash != $new_hash) || ($old_status != $new_status)) {
      // set the datetime
      $date = date('Y-m-d H:i:s');
      $data = array(
          'page_id' => self::$PAGE_ID,
          'section_id' => self::$SECTION_ID,
          'content' => self::$SECTION_CONTENT,
          'author' => $cms->getUserDisplayName(),
          'editor' => self::$EDITOR_NAME,
          'status' => 'DRAFT',
          'publish' => 'PRIVATE',
          'supervisors' => ''
          );
      // save the draft
      if (false === ($wysiwygArchive->insertEditorial($data, self::$ARCHIVE_ID))) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }
      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'CHANGED',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', $date, $new_status)
      );
      $this->setMessage($I18n->translate('<p>The content for the SECTION ID {{ section_id }} was successfull saved.</p>',
          array('section_id' => self::$SECTION_ID)), __METHOD__, __LINE__);
    }

    if (isset($_GET['approval']) && ($_GET['approval'] == 1)) {
      // editor want to publish the text!
      return $this->approveSection();
    }
    if (self::$EMAIL_SEND == 1) {
      return $this->sendEMail();
    }
    return true;
  } // checkEditorialSection()

  /**
   * Get the department ID for the actual page ID
   *
   * @return boolean|Ambigous <boolean, \phpManufaktur\extendedWYSIWYG\Data\number, number>
   */
  protected function getDepartmentId() {
    global $I18n;

    // get the department
    $editorDepartment = new editorDepartment();
    if (false === ($department_id = $editorDepartment->getDepartmentIdForPageId(self::$PAGE_ID))) {
      $this->errorExit($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    if ($department_id < 1) {
      $this->errorExit($I18n->translate('<p>Can\'t get the department ID for page ID {{ page_id }}.</p>',
          array('page_id' => self::$PAGE_ID)), __METHOD__, __LINE__);
      return false;
    }
    return $department_id;
  } // getDepartment()

  /**
   * The editor wants to publish this section
   *
   * @return boolean
   */
  protected function approveSection() {
    global $cms;
    global $I18n;

    $editorTeam = new editorTeam();
    if (false === ($editor = $editorTeam->selectEditorByName(self::$EDITOR_NAME))) {
      $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    if ($editorTeam->checkPermission($editor['permissions'], editorTeam::PERMISSION_RELEASE_BY_OWN)) {
      // need no approval - release the section!
      $author = $cms->getUserDisplayName();
      $wysiwygArchive = new wysiwygArchive();
      if (!$wysiwygArchive->insert(self::$PAGE_ID, self::$SECTION_ID, self::$SECTION_CONTENT, $author, 'ACTIVE', self::$ARCHIVE_ID)) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }
      // ok - now we have to update the regular CMS WYSIWYG record!
      $wysiwygSection = new wysiwygSection();
      if (!$wysiwygSection->update(self::$SECTION_ID, self::$SECTION_CONTENT)) {
        $this->errorExit($wysiwygSection->getError(), __METHOD__, __LINE__);
        return false;
      }

      $this->setMessage($I18n->translate('<p>The section with the ID {{ section_id }} was successfull published!</p>',
          array('section_id' => self::$SECTION_ID)), __METHOD__, __LINE__);

      // save the section to archive?
      if (self::$CREATE_ARCHIVE_FILES) {
        $archiveFile = new archiveFile();
        $archive_file = '';
        if (!$archiveFile->save(self::$ARCHIVE_ID, $archive_file)) {
          $this->errorExit($archiveFile->getError(), __METHOD__, __LINE__);
          return false;
        }
        $this->setMessage($I18n->translate('<p>Saved the section as archive file {{ archive_file }}.</p>',
            array('archive_file' => $archive_file)), __METHOD__, __LINE__);
      }

      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'CHANGED',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', date('Y-m-d H:i:s'), 'ACTIVE')
      );
    }
    else {
      // need approval
      $date = date('Y-m-d H:i:s');

      // get the editor infos
      $editorTeam = new editorTeam();
      if (false === ($editor = $editorTeam->selectEditorByName(self::$EDITOR_NAME))) {
        $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }

      $supervisors = explode(',', $editor['supervisors']);

      if ($editorTeam->checkPermission($editor['permissions'], editorTeam::PERMISSION_RELEASE_CHIEF_EDITOR_ONLY)) {
      	// send emails only to the CHIEF and his SUB!
      	$check_array = $supervisors;
      	foreach ($check_array as $supervisor) {
      		if (!$editorTeam->isChiefEditor($supervisor))
      			unset($supervisors[array_search($supervisor, $supervisors)]);
      	}
      }

      $wysiwygArchive = new wysiwygArchive();
      $data = array(
          'page_id' => self::$PAGE_ID,
          'section_id' => self::$SECTION_ID,
          'content' => self::$SECTION_CONTENT,
          'author' => $cms->getUserDisplayName(),
          'editor' => self::$EDITOR_NAME,
          'status' => 'PENDING',
          'publish' => 'APPROVAL',
          'supervisors' => implode(',', $supervisors) //$editor['supervisors']
          );
      // save the draft
      if (false === ($wysiwygArchive->insertEditorial($data, self::$ARCHIVE_ID))) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }

      // init swiftMailer
      $swiftMailer = new swiftMailer();
      if (!$swiftMailer->init()) {
        $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
        return false;
      }

      $Pages = new Pages();
      $subject = $I18n->translate('Requiring approval for publishing content');

      $data = array(
          'page_title' => $Pages->selectPageTitle(self::$PAGE_ID),
          'section_id' => self::$SECTION_ID,
          'editor' => $cms->getUserDisplayName(),
          'page_url' => sprintf('%s?%s#%s', self::$MODIFY_PAGE_URL, http_build_query(array(
              'page_id' => self::$PAGE_ID
              )),
              sprintf('%s%s', self::ANCHOR, self::$SECTION_ID)),
          'message' => self::$EMAIL_TEXT
          );
      $body = $this->getTemplate('approval.dwoo', $data);

      // init the messages
      $wysiwygMessages = new wysiwygMessages();


      foreach ($supervisors as $supervisor_name) {
        // save the message
        $data = array(
            'section_id' => self::$SECTION_ID,
            'page_id' => self::$PAGE_ID,
            'archive_id' => self::$ARCHIVE_ID,
            'department_id' => $this->getDepartmentId(),
            'content' => $body,
            'from_editor' => self::$EDITOR_NAME,
            'to_editor' => $supervisor_name,
            'status' => 'PENDING'
            );
        if (!$wysiwygMessages->insert($data)) {
          $this->errorExit($wysiwygMessages->getError(), __METHOD__, __LINE__);
          return false;
        }
        // get the email address
        if (false === ($email = $editorTeam->selectEMailByEditorName($supervisor_name))) {
          $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
          return false;
        }
        // send the mail
        if (!$swiftMailer->sendServerMail($subject, $body, $email)) {
          $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
          return false;
        }
      }
      $this->setMessage($I18n->translate('<p>The section id {{ section_id }} is marked for approval by your supervisors {{ supervisors }}.</p><p>Look ahead for further informations!</p>',
          array('section_id' => self::$SECTION_ID, 'supervisors' => $editor['supervisors'])), __METHOD__, __LINE__);
      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'RELOAD',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', date('Y-m-d H:i:s'), 'PENDING')
      );
      return true;
    }

    return true;
  } // approveSection()

  protected function sendEMail() {
    global $I18n;

    $editorDepartment = new editorDepartment();
    if (false === ($department_id = $editorDepartment->getDepartmentIdForPageId(self::$PAGE_ID))) {
      $this->errorExit($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }

    $editorTeam = new editorTeam();
    if (false === ($editors = $editorTeam->selectEditorsOfDepartment($department_id))) {
      $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }

    // init swiftMailer
    $swiftMailer = new swiftMailer();
    if (!$swiftMailer->init()) {
      $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
      return false;
    }

    $Pages = new Pages();
    $Users = new Users();

    $subject = $I18n->translate('Message from {{ editor }}', array('editor' => $Users->getUserDisplayName(self::$EDITOR_NAME)));

    $data = array(
        'page_title' => $Pages->selectPageTitle(self::$PAGE_ID),
        'section_id' => self::$SECTION_ID,
        'editor' => $Users->getUserDisplayName(self::$EDITOR_NAME),
        'page_url' => sprintf('%s?%s#%s', self::$MODIFY_PAGE_URL, http_build_query(array(
            'page_id' => self::$PAGE_ID
        )),
            sprintf('%s%s', self::ANCHOR, self::$SECTION_ID)),
        'message' => self::$EMAIL_TEXT
    );
    $body = $this->getTemplate('email.dwoo', $data);

    // init the messages
    $wysiwygMessages = new wysiwygMessages();

    foreach ($editors as $editor_id) {
      if (false == ($email = $editorTeam->selectEMailByEditorId($editor_id))) {
        $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }

      // save the message
      $data = array(
          'section_id' => self::$SECTION_ID,
          'page_id' => self::$PAGE_ID,
          'archive_id' => self::$ARCHIVE_ID,
          'department_id' => $this->getDepartmentId(),
          'content' => $body,
          'from_editor' => self::$EDITOR_NAME,
          'to_editor' => $Users->getUserName($email),
          'status' => 'PENDING'
      );
      if (!$wysiwygMessages->insert($data)) {
        $this->errorExit($wysiwygMessages->getError(), __METHOD__, __LINE__);
        return false;
      }
      // send the mail
      if (!$swiftMailer->sendServerMail($subject, $body, $email)) {
        $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
        return false;
      }
    }

    $this->setMessage($I18n->translate('<p>Your message was send to the members of the department!</p>'), __METHOD__, __LINE__);
    return true;
  } // sendEMail()


  protected function checkEditorAction() {
    global $I18n;

    switch (self::$EDITOR_ACTION):
    case 'APPROVE':
      // APPROVE the publishing of a section
      switch (self::$EDITOR_RESPONSE):
      case 'PUBLISHED':
        // the supervisor want to publish the content
        return $this->actionPublish();
      case 'PROOFREAD':
      case 'REFUSED':
        // a supervisor has rejected the content
        return $this->actionRejected();
      default:
        // the editor response is not defined
        $this->errorExit($I18n->translate('<p>The editor response <b>{{ response }}</b> is undefined!</p><p>Please contact the support.</p>',
          array('response' => self::$EDITOR_RESPONSE)), __METHOD__, __LINE__);
        return false;
      endswitch;
      break;
    default:
      // the editor action is not defined
      $this->errorExit($I18n->translate('<p>The editor action <b>{{ action }}</b> is undefined!</p><p>Please contact the support.</p>',
          array('action' => self::$EDITOR_ACTION)), __METHOD__, __LINE__);
      return false;
    endswitch;

    return true;
  } // checkEditorAction()

  protected function actionPublish() {
    global $I18n;

    // first we need the archive
    $wysiwygArchive = new wysiwygArchive();
    if (false === ($archive = $wysiwygArchive->select(self::$ARCHIVE_ID))) {
      $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }
    $supervisors = explode(',', $archive['supervisors']);

    /*
    // delete the approving editor from the supervisors list
    unset($supervisors[array_search(self::$EDITOR_NAME, $supervisors)]);
		*/

    // explicit check of the approval
    $editor = $archive['editor'];
    $editorTeam = new editorTeam();
    if (false === ($editorData = $editorTeam->selectEditorByName($archive['editor']))) {
    	$this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
    	return false;
    }
    if ($editorTeam->checkPermission($editorData['permissions'], editorTeam::PERMISSION_RELEASE_ONE_SUPERVISOR)) {
    	// Release by ONE Supervisor
    	// unset all other approving editors because the article is already approved!
    	$supervisors = array();
    }
    elseif ($editorTeam->checkPermission($editorData['permissions'], editorTeam::PERMISSION_RELEASE_CHIEF_EDITOR_ONLY)) {
    	// Release by CHIEF editor only
    	if ($editorTeam->isChiefEditor(self::$EDITOR_NAME)) {
    		// unset all supervisors because the article can be released!
    		$supervisors = array();
    	}
    	else {
    		$this->errorExit($I18n->translate('Sorry, you are not allowed to release this article!'), __METHOD__, __LINE__);
    		return false;
    	}
    }
    elseif ($editorTeam->checkPermission($editorData['permissions'], editorTeam::PERMISSION_RELEASE_TWO_SUPERVISOR)) {
    	// Release by TWO supervisors
    	$approved = (!empty($archive['approved'])) ? explode(',', $archive['approved']) : array();
    	if (count($approved) > 0) {
    		// article is already approved by another supervisor and can be released!
    		$supervisors = array();
    	}
    	else {
    		// delete the approving editor from the supervisors list
    		unset($supervisors[array_search(self::$EDITOR_NAME, $supervisors)]);
    	}
    }
    else {
    	$this->errorExit($I18n->translate('Ooops, missing a valid release method! Please contact the support!'), __METHOD__, __LINE__);
    	return false;
    }

    if (count($supervisors) == 0) {
      // ok - the section can be published!
      $approved = (!empty($archive['approved'])) ? explode(',', $archive['approved']) : array();
      $approved[] = self::$EDITOR_NAME;
      // update the data
      $data = array(
          'status' => 'DRAFT',
          'publish' => 'PUBLISHED',
          'supervisors' => '',
          'approved' => implode(',', $approved)
      );
      if (!$wysiwygArchive->update(self::$ARCHIVE_ID, $data)) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }
      // now we can publish the section
      $Users = new Users();
      $author = $Users->getUserDisplayName($archive['editor']);
      $email = $Users->getUserEMail($archive['editor']);

      self::$SECTION_CONTENT = $archive['content'];
      if (!$wysiwygArchive->insert(self::$PAGE_ID, self::$SECTION_ID, self::$SECTION_CONTENT, $author, 'ACTIVE', self::$ARCHIVE_ID)) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }
      // ok - now we have to update the regular CMS WYSIWYG record!
      $wysiwygSection = new wysiwygSection();
      if (!$wysiwygSection->update(self::$SECTION_ID, self::$SECTION_CONTENT)) {
        $this->errorExit($wysiwygSection->getError(), __METHOD__, __LINE__);
        return false;
      }
      $this->setMessage($I18n->translate('<p>The section with the ID {{ section_id }} was successfull published!</p>',
          array('section_id' => self::$SECTION_ID)), __METHOD__, __LINE__);

      // save the section to archive?
      if (self::$CREATE_ARCHIVE_FILES) {
        $archiveFile = new archiveFile();
        $archive_file = '';
        if (!$archiveFile->save(self::$ARCHIVE_ID, $archive_file)) {
          $this->errorExit($archiveFile->getError(), __METHOD__, __LINE__);
          return false;
        }
        $this->setMessage($I18n->translate('<p>Saved the section as archive file {{ archive_file }}.</p>',
            array('archive_file' => $archive_file)), __METHOD__, __LINE__);
      }

      // now we have to inform the editors about the publishing
      $emails = array();
      // add the article editor
      $emails[] = $Users->getUserEMail($archive['editor']);
      // ... and all approving supervisors
      foreach ($approved as $name)
        $emails[] = $Users->getUserEMail($name);

      // init swiftMailer
      $swiftMailer = new swiftMailer();
      if (!$swiftMailer->init()) {
        $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
        return false;
      }

      $Pages = new Pages();
      $subject = $I18n->translate('Section published');

      $data = array(
          'page_title' => $Pages->selectPageTitle(self::$PAGE_ID),
          'section_id' => self::$SECTION_ID,
          'editor' => $author,
          'page_url' => sprintf('%s?%s#%s', self::$MODIFY_PAGE_URL, http_build_query(array(
              'page_id' => self::$PAGE_ID
              )),
              sprintf('%s%s', self::ANCHOR, self::$SECTION_ID)),
          'message' => self::$EMAIL_TEXT
      );
      $body = $this->getTemplate('published.dwoo', $data);

      // init the messages
      $wysiwygMessages = new wysiwygMessages();

      foreach ($emails as $email) {
        // save the message
        $data = array(
            'section_id' => self::$SECTION_ID,
            'page_id' => self::$PAGE_ID,
            'archive_id' => self::$ARCHIVE_ID,
            'department_id' => $this->getDepartmentId(),
            'content' => $body,
            'from_editor' => self::$EDITOR_NAME,
            'to_editor' => $Users->getUserName($email),
            'status' => 'PENDING'
            );
        if (!$wysiwygMessages->insert($data)) {
          $this->errorExit($wysiwygMessages->getError(), __METHOD__, __LINE__);
          return false;
        }
        // send the mail
        if (!$swiftMailer->sendServerMail($subject, $body, $email)) {
          $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
          return false;
        }
      }

      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'RELOAD',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', date('Y-m-d H:i:s'), 'ACTIVE')
      );
      return true;
    }
    else {
      // there are further supervisors who must approve the section
      $approved = (!empty($archive['approved'])) ? explode(',', $archive['approved']) : array();
      // update the archive
      $approved[] = self::$EDITOR_NAME;
      // update the data
      $data = array(
          'supervisors' => implode(',', $supervisors),
          'approved' => implode(',', $approved)
      );
      if (!$wysiwygArchive->update(self::$ARCHIVE_ID, $data)) {
        $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
        return false;
      }

      $Users = new Users();

      // now we have to inform the editors about the publishing
      $emails = array();
      // add the article editor
      $emails[] = $Users->getUserEMail($archive['editor']);
      // ... and all approving supervisors
      foreach ($approved as $name)
        $emails[] = $Users->getUserEMail($name);

      // init swiftMailer
      $swiftMailer = new swiftMailer();
      if (!$swiftMailer->init()) {
        $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
        return false;
      }

      $Pages = new Pages();
      $subject = $I18n->translate('Section approved');

      $data = array(
          'page_title' => $Pages->selectPageTitle(self::$PAGE_ID),
          'section_id' => self::$SECTION_ID,
          'editor' => $Users->getUserDisplayName($archive['editor']),
          'supervisor' => $Users->getUserDisplayName(self::$EDITOR_NAME),
          'page_url' => sprintf('%s?%s#%s', self::$MODIFY_PAGE_URL, http_build_query(array(
              'page_id' => self::$PAGE_ID
          )),
              sprintf('%s%s', self::ANCHOR, self::$SECTION_ID)),
          'message' => self::$EMAIL_TEXT
      );
      $body = $this->getTemplate('approved.dwoo', $data);

      // init the messages
      $wysiwygMessages = new wysiwygMessages();

      foreach ($emails as $email) {
        // save the message
        $data = array(
            'section_id' => self::$SECTION_ID,
            'page_id' => self::$PAGE_ID,
            'archive_id' => self::$ARCHIVE_ID,
            'department_id' => $this->getDepartmentId(),
            'content' => $body,
            'from_editor' => self::$EDITOR_NAME,
            'to_editor' => $Users->getUserName($email),
            'status' => 'PENDING'
        );
        if (!$wysiwygMessages->insert($data)) {
          $this->errorExit($wysiwygMessages->getError(), __METHOD__, __LINE__);
          return false;
        }
        // send the mail
        if (!$swiftMailer->sendServerMail($subject, $body, $email)) {
          $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
          return false;
        }
      }

      // set the response
      self::$SECTION_RESULT = array(
          'status' => 'RELOAD',
          'archive_id' => self::$ARCHIVE_ID,
          'option' => sprintf('%s | %s', date('Y-m-d H:i:s'), 'ACTIVE')
      );
      return true;
    }

    // now we update the archive record

  } // actionPublish()

  protected function actionRejected() {
    global $I18n;

    // first we need the archive
    $wysiwygArchive = new wysiwygArchive();
    if (false === ($archive = $wysiwygArchive->select(self::$ARCHIVE_ID))) {
      $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }
    $supervisors = explode(',', $archive['supervisors']);
    // delete the rejecting editor from the supervisors list
    unset($supervisors[array_search(self::$EDITOR_NAME, $supervisors)]);

    // update the data
    $data = array(
        'status' => 'DRAFT',
        'publish' => 'PRIVATE',
        'supervisors' => implode(',', $supervisors),
        'approved' => (self::$EDITOR_RESPONSE == 'REFUSED') ? self::$EDITOR_NAME : ''
    );
    if (!$wysiwygArchive->update(self::$ARCHIVE_ID, $data)) {
      $this->errorExit($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }

    $Users = new Users();

    $approved = (!empty($archive['approved'])) ? explode(',', $archive['approved']) : array();
    $supervisors = (!empty($archive['supervisors'])) ? explode(',', $archive['supervisors']) : array();
    // now we have to inform the editors about the publishing
    $emails = array();
    // add the article editor
    $emails[] = $Users->getUserEMail($archive['editor']);
    // ... and all approving supervisors
    foreach ($approved as $name)
      $emails[] = $Users->getUserEMail($name);
    foreach ($supervisors as $name)
      $emails[] = $Users->getUserEMail($name);


    // init swiftMailer
    $swiftMailer = new swiftMailer();
    if (!$swiftMailer->init()) {
      $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
      return false;
    }

    $Pages = new Pages();
    $subject = $I18n->translate('Section rejected');

    $data = array(
        'page_title' => $Pages->selectPageTitle(self::$PAGE_ID),
        'section_id' => self::$SECTION_ID,
        'editor' => $Users->getUserDisplayName($archive['editor']),
        'supervisor' => $Users->getUserDisplayName(self::$EDITOR_NAME),
        'response' => self::$EDITOR_RESPONSE,
        'page_url' => sprintf('%s?%s#%s', self::$MODIFY_PAGE_URL, http_build_query(array(
            'page_id' => self::$PAGE_ID
        )),
            sprintf('%s%s', self::ANCHOR, self::$SECTION_ID)),
        'message' => self::$EMAIL_TEXT
    );
    $body = $this->getTemplate('rejected.dwoo', $data);

    // init the messages
    $wysiwygMessages = new wysiwygMessages();

    foreach ($emails as $email) {
      // save the message
      $data = array(
          'section_id' => self::$SECTION_ID,
          'page_id' => self::$PAGE_ID,
          'archive_id' => self::$ARCHIVE_ID,
          'department_id' => $this->getDepartmentId(),
          'content' => $body,
          'from_editor' => self::$EDITOR_NAME,
          'to_editor' => $Users->getUserName($email),
          'status' => 'PENDING'
      );
      if (!$wysiwygMessages->insert($data)) {
        $this->errorExit($wysiwygMessages->getError(), __METHOD__, __LINE__);
        return false;
      }
      // send the mail
      if (!$swiftMailer->sendServerMail($subject, $body, $email)) {
        $this->errorExit($swiftMailer->getError(), __METHOD__, __LINE__);
        return false;
      }
    }

    // set the response
    self::$SECTION_RESULT = array(
        'status' => 'RELOAD',
        'archive_id' => self::$ARCHIVE_ID,
        'option' => sprintf('%s | %s', date('Y-m-d H:i:s'), 'DRAFT')
    );
    return true;


  } // actionRejected()

  /**
   * EXEC procedure of the class controlContent
   */
  public function exec() {
    global $I18n;
    global $cms;

    $this->checkRequests();

    $Users = new Users();
    if ($Users->isAdministrator(self::$EDITOR_NAME)) {
    	// admins are out of the editorial system!
    	self::$EDITORIAL_SYSTEM_IS_ACTIVE = false;
    }

    $this->checkPageSettings();
    $this->checkTeaser();

    if (self::$EDITORIAL_SYSTEM_IS_ACTIVE) {
    	if (self::$EDITOR_ACTION != 'NONE') {
        $this->checkEditorAction();
      }
      else {
        $this->checkEditorialSection();
      }
    }
    else
      $this->checkSection();

    // quit the script and return all messages
    if (!$this->isMessage()) {
      $this->setMessage($I18n->translate('<p>Nothing changed.</p>'), __METHOD__, __LINE__);
    }
    // format the messages
    $messages = sprintf('<div class="wysiwyg_message">%s</div>', $this->getMessage());
    // create the result array
    $result = array(
        'status' => 'OK',
        'message' => $messages,
        'teaser' => self::$TEASER_RESULT,
        'section' => self::$SECTION_RESULT
    );
    exit(json_encode($result));
  }

} // class controlContent


// init and execute the control
$controlContent = new controlContent();
$controlContent->exec();
