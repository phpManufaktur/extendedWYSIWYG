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

use phpManufaktur\extendedWYSIWYG\Data\editorDepartment;
use phpManufaktur\extendedWYSIWYG\Data\editorTeam;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\Data\LEPTON as LEPTON;

class controlAccess extends boneClass {

  protected static $USERNAME = null;
  protected static $PAGE_ID = null;
  protected static $SECTION_ID = null;
  protected static $EDITORIAL_SYSTEM_ACTIVE = null;
  protected static $DEPARTMENT_ID = null;
  protected static $EDITOR = array();

  public function __construct($page_id, $section_id) {
    global $cms;

    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
    // init configuration
    $config = new wysiwygConfiguration();
    self::$EDITORIAL_SYSTEM_ACTIVE = $config->getValue('cfgUseEditorialDepartment');
    // get the username
    self::$USERNAME = $cms->getUserLoginName();
  } // __construct()

  /**
   * Check if the user is a administrator
   *
   * @return boolean
   */
  protected function isAdministrator() {
    global $I18n;

    $Users = new LEPTON\Users();
    if ($Users->isAdministrator(self::$USERNAME)) {
      // administrators have always access!
      // init the controlEditor
      $controlEditor = new controlEditor(self::$USERNAME);
      $controlEditor->activity($I18n->translate('Access as admin the page {{ page_id }}',
          array('page_id' => self::$PAGE_ID)));
      return true;
    }
    return false;
  }

  /**
   * Check if the user is chief editor
   *
   * @return boolean
   */
  protected function isChiefEditor() {
    global $I18n;

    $editorTeam = new editorTeam();
    if ($editorTeam->isChiefEditor(self::$USERNAME)) {
      // chief editors and sub chief editors have always access
      // init the controlEditor
      $controlEditor = new controlEditor(self::$USERNAME);
      $controlEditor->activity($I18n->translate('Access as chief editor to the page {{ page_id }}',
          array('page_id' => self::$PAGE_ID)));
      return true;
    }
    return false;
  } // isChiefEditor()

  /**
   * Check if a department exists for the page
   *
   * @return boolean
   */
  protected function existsDepartmentForPage() {
    global $I18n;

    // init the department access
    $editorDepartment = new editorDepartment();
    if (false === (self::$DEPARTMENT_ID = $editorDepartment->getDepartmentIdForPageId(self::$PAGE_ID))) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    if (self::$DEPARTMENT_ID < 1) {
      // there exists no department for this page id!
      $this->setInfo($I18n->translate('Access to page {{ page_id }} denied: no department assigned',
          array('page_id' => self::$PAGE_ID)), __METHOD__, __LINE__);
      return false;
    }
    return true;
  }

  /**
   * Check if the user is member of the desired department
   *
   * @return boolean
   */
  protected function isEditorForDepartment() {
    global $I18n;

    $editorTeam = new editorTeam();
    if (false === ($editorTeam->isEditorForDepartment(self::$DEPARTMENT_ID, self::$USERNAME, self::$EDITOR))) {
      if ($editorTeam->isError()) {
        $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }
      // init the controlEditor
      $controlEditor = new controlEditor(self::$USERNAME);
      $controlEditor->activity($I18n->translate('Access to page {{ page_id }} denied: the user is not member of the assigned department',
          array('page_id' => self::$PAGE_ID)));
      return false;
    }
    return true;
  }

  /**
   * Check if the user has the permission to view or edit a section
   *
   * @return boolean
   */
  public function checkSectionAccess() {
    global $I18n;

    if (!self::$EDITORIAL_SYSTEM_ACTIVE)
      // the editorial system is inactive!
      return true;

    if ($this->isAdministrator())
      return true;

    if ($this->isChiefEditor())
      return true;

    if (!$this->existsDepartmentForPage())
      return false;

    if (!$this->isEditorForDepartment())
      return false;

    $editorTeam = new editorTeam();
    // init the controlEditor
    $controlEditor = new controlEditor(self::$USERNAME);

    if (!$editorTeam->checkPermission(self::$EDITOR['permissions'], editorTeam::PERMISSION_SECTION_VIEW) &&
        !$editorTeam->checkPermission(self::$EDITOR['permissions'], editorTeam::PERMISSION_SECTION_EDIT)) {
      $controlEditor->activity($I18n->translate('Access to page {{ page_id }} denied: the editor is member of the department {{ department_id }} but has not rights to view or edit a section.',
          array('page_id' => self::$PAGE_ID, 'department_id' => self::$DEPARTMENT_ID)));
      return false;
    }

    // Success!
    $controlEditor->activity($I18n->translate('Access to page {{ page_id }} granted for the editor.',
        array('page_id' => self::$PAGE_ID)));
    return true;
  } // checkSectionAccess()

  /**
   * Check if the user is allowed to delete the section
   *
   * @return boolean
   */
  public function checkSectionDelete() {
    global $I18n;

    if (!self::$EDITORIAL_SYSTEM_ACTIVE)
      // the editorial system is inactive!
      return true;

    if ($this->isAdministrator())
      return true;

    if ($this->isChiefEditor())
      return true;

    if (!$this->existsDepartmentForPage())
      return false;

    if (!$this->isEditorForDepartment())
      return false;

    // init the controlEditor
    $controlEditor = new controlEditor(self::$USERNAME);

    $editorTeam = new editorTeam();
    if (!$editorTeam->checkPermission(self::$EDITOR['permissions'], editorTeam::PERMISSION_SECTION_DELETE)) {
      $controlEditor->activity($I18n->translate('Access to page {{ page_id }} denied: the editor is member of the department {{ department_id }} but has not rights to delete a section.',
          array('page_id' => self::$PAGE_ID, 'department_id' => self::$DEPARTMENT_ID)));
      return false;
    }
    // Success!
    $controlEditor->activity($I18n->translate('The editor has deleted the section {{ section_id }} of the page {{ page_id }}.',
        array('page_id' => self::$PAGE_ID, 'section_id' => self::$SECTION_ID)));
    return true;
  }

  /**
   * Check if the user is allowed to add/create a new section
   *
   * @return boolean
   */
  public function checkSectionAdd() {
    global $I18n;

    if (!self::$EDITORIAL_SYSTEM_ACTIVE)
      // the editorial system is inactive!
      return true;

    // init the controlEditor
    $controlEditor = new controlEditor(self::$USERNAME);

    if ($this->isAdministrator())
      return true;

    if ($this->isChiefEditor())
      return true;

    if (!$this->existsDepartmentForPage())
      return false;

    if (!$this->isEditorForDepartment())
      return false;

    $editorTeam = new editorTeam();
    if (!$editorTeam->checkPermission(self::$EDITOR['permissions'], editorTeam::PERMISSION_SECTION_CREATE)) {
      $controlEditor->activity($I18n->translate('Access to page {{ page_id }} denied: the editor is member of the department {{ department_id }} but has not rights to add/create a section.',
          array('page_id' => self::$PAGE_ID, 'department_id' => self::$DEPARTMENT_ID)));
      return false;
    }
    // Success!
    $controlEditor->activity($I18n->translate('Added the section {{ section_id }} to the page {{ page_id }}.',
        array('page_id' => self::$PAGE_ID, 'section_id' => self::$SECTION_ID)));
    return true;
  }

} // class controlAccess