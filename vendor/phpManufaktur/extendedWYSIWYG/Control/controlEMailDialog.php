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

use phpManufaktur\extendedWYSIWYG\Data\editorTeam;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\View;

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

/**
 * Section content control - called by jQuery modifySection.js
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 */
class controlEMailDialog extends boneModifySection {

  protected static $EDITOR_NAME = null;
  protected static $EMAIL_TYPE = null;

  /**
   * Set the error message, init the view and return the error dialog
   *
   * @return string
   */
  protected function errorExit($error, $method, $line) {
    $this->setError($error, $method, $line);
    $View = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
    $prompt = $View->promptError($this->getError());
    exit($prompt);
  } // errorExit()

  protected function checkRequests() {
    global $I18n;

    if (!isset($_GET['section_id']) ||
        !isset($_GET['page_id']) ||
        !isset($_GET['editor_name']) ||
        !isset($_GET['email_type'])
    ) {
      $this->errorExit($I18n->translate('[ {{ file }} ] Missing essential parameters!',
          array('file' => basename(__FILE__))), __METHOD__, __LINE__);
      return false;
    }
    self::$PAGE_ID = (int) $_GET['page_id'];
    self::$SECTION_ID = (int) $_GET['section_id'];
    self::$EDITOR_NAME = $_GET['editor_name'];
    self::$EMAIL_TYPE = $_GET['email_type'];
    return true;
  } // checkRequests()

  public function exec() {
    global $I18n;

    $this->checkRequests();
    $View = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
    if (self::$EMAIL_TYPE == 'approval') {
      // handle as approval request
      $editorTeam = new editorTeam();
      if (false === ($editor = $editorTeam->selectEditorByName(self::$EDITOR_NAME))) {
        $this->errorExit($editorTeam->getError(), __METHOD__, __LINE__);
        exit($View->showError($this->getError()));
      }
      $release_by_own = (int) $editorTeam->checkPermission($editor['permissions'], editorTeam::PERMISSION_RELEASE_BY_OWN);
      $data = array(
          'email' => array(
              'name' => self::REQUEST_EMAIL_TEXT.'_'.self::$SECTION_ID,
              'text' => ''
              ),
          'permissions' => array(
              'release_by_own' => $release_by_own
              )
          );
      exit($View->dialogApprovalEMail($data));
    }
    elseif (self::$EMAIL_TYPE == 'email') {
      // handle as regular email
      $data = array(
          'email' => array(
              'name' => self::REQUEST_EMAIL_TEXT.'_'.self::$SECTION_ID,
              'text' => ''
          )
      );
      exit($View->dialogEMail($data));
    }
    else {
      // unknown email type
      $this->errorExit($I18n->translate('[ {{ file }} ] The email type {{ type }} is unknown!',
          array('file' => __METHOD__, 'type' => self::$EMAIL_TYPE)), __METHOD__, __LINE__);
    }
  } // exec()

} // class controlEMailDialog

$control = new controlEMailDialog(-1,-1);
$control->exec();