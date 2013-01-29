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

use phpManufaktur\CMS\Bridge\Data\LEPTON\Pages;
use phpManufaktur\extendedWYSIWYG\Data\editorDepartment;
use phpManufaktur\extendedWYSIWYG\Data\editorTeam;
use phpManufaktur\CMS\Bridge\Data\LEPTON\Settings;
use phpManufaktur\extendedWYSIWYG\Data\logfile;
use Markdown\Markdown;
use phpManufaktur\CMS\Bridge\Data\mysqlVersion;
use phpManufaktur\extendedWYSIWYG\Data\addonVersion;
use phpManufaktur\CMS\Bridge\Data\LEPTON\Users;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\Data\LEPTON;
use phpManufaktur\extendedWYSIWYG\View\viewSettings;

class controlSettings extends boneSettings {

  /**
   * Action handler for controlSettings and viewSettings
   *
   * @return string
   */
  public function action() {
    if (!isset($_SESSION[self::SESSION_SETTINGS_AUTHENTICATED]) && (!isset($_REQUEST[self::REQUEST_ACTION]) ||
        ($_REQUEST[self::REQUEST_ACTION] != self::ACTION_LOGIN_CHECK))) {
      $_REQUEST[self::REQUEST_ACTION] = self::ACTION_LOGIN;
    }

    $action = (isset($_REQUEST[self::REQUEST_ACTION])) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;

    switch ($action):
    case self::ACTION_SETTINGS:
      $View = new viewSettings();
      $result = $View->dialogSetttings($action);
      break;
    case self::ACTION_LOGIN:
      // user must login as administrator!
      $result = $this->actionLogin();
      break;
    case self::ACTION_LOGIN_CHECK:
      // check the login
      $result = $this->actionLoginCheck();
      break;
    case self::ACTION_LOGOUT:
      $result = $this->actionLogout();
      break;
    case self::ACTION_CHANGE_LEVEL:
      $result = $this->actionChangeLevel();
      break;
    case self::ACTION_EDITORIAL:
      $subaction = (isset($_REQUEST[self::REQUEST_SUB_ACTION])) ? $_REQUEST[self::REQUEST_SUB_ACTION] : self::ACTION_EDITORIAL_TEAM;
      switch ($subaction):
      case self::ACTION_EDITORIAL_DEPARTMENT:
        // all actions around editorial department
        $subsubaction = (isset($_REQUEST[self::REQUEST_SUB_SUB_ACTION])) ? $_REQUEST[self::REQUEST_SUB_SUB_ACTION] : self::ACTION_DEFAULT;
        switch ($subsubaction):
        case self::ACTION_ADD_DEPARTMENT:
          // add a department
          $result = $this->actionAddDepartment();
          break;
        case self::ACTION_EDIT_DEPARTMENT:
          // edit a department
          $result = $this->actionEditDepartment();
          break;
        case self::ACTION_CHECK_DEPARTMENT:
          // check changes of a department
          $result = $this->actionCheckDepartment();
          break;
        default:
          // show the default department dialog
          $result = $this->actionEditorialDepartment();
          break;
        endswitch;
        break;
      case self::ACTION_EDITORIAL_TEAM:
      default:
        // all actions around editorial team
        $subsubaction = (isset($_REQUEST[self::REQUEST_SUB_SUB_ACTION])) ? $_REQUEST[self::REQUEST_SUB_SUB_ACTION] : self::ACTION_DEFAULT;
        switch ($subsubaction):
        case self::ACTION_EDIT_EDITOR:
          // edit an editor
          $result = $this->actionEditEditor();
          break;
        case self::ACTION_ADD_EDITOR:
          // add a new editor
          $result = $this->actionAddEditor();
          break;
        case self::ACTION_CHECK_EDITOR:
          // check the settings for an editor
          $result = $this->actionCheckEditor();
          break;
        default:
          // show the default editorial team dialog
          $result = $this->actionEditorialTeam();
          break;
        endswitch;
        break;
      endswitch;
      break;
    case self::ACTION_START:
    default:
       $result = $this->actionStart();
       break;
    endswitch;

    if ($this->isError()) {
      // prompt error
      $View = new viewSettings();
      $View->setError($this->getError(), __METHOD__, __LINE__);
      echo $View->show($action, $this->getError());
    }
    else {
      // prompt the complete settings page
      echo $result;
    }
  } // action()

  /**
   * Action procedure for the Login
   *
   * @return string
   */
  protected function actionLogin() {
    $data = array(
      'form' => array(
          'name' => 'login_dialog',
          'action' => self::$SETTINGS_URL
          ),
      'action' => array(
          'name' => self::REQUEST_ACTION,
          'value' => self::ACTION_LOGIN_CHECK
          ),
      'message' => array(
          'active' => (int) $this->isMessage(),
          'content' => $this->getMessage()
          ),
      'login' => array(
          'username' => array(
              'name' => self::REQUEST_USERNAME,
              'value' => isset($_REQUEST[self::REQUEST_USERNAME]) ? $_REQUEST[self::REQUEST_USERNAME] : ''
              ),
          'password' => array(
              'name' => self::REQUEST_PASSWORD,
              'value' => ''
              ),
          ),
      );
    $View = new viewSettings();
    return $View->dialogLogin(self::ACTION_START, $data);
  } // actionLogin()

  /**
   * Action procedure for the Login Check
   *
   * @return string
   */
  protected function actionLoginCheck() {
    $View = new viewSettings();
    if ($this->checkLoginAsAdmin()) {
      // ok - go to the start dialog
      return $this->actionStart();
    }
    // retry login
    $data = array(
        'username' => isset($_REQUEST[self::REQUEST_USERNAME]) ? $_REQUEST[self::REQUEST_USERNAME] : '',
        'message' => $this->getMessage()
    );
    return $View->dialogLogin(self::ACTION_START, $data);
  } // actionLoginCheck()

  /**
   * Action procedure for the Logout
   *
   * @return string
   */
  protected function actionLogout() {
    global $I18n;
    unset($_SESSION[self::SESSION_SETTINGS_AUTHENTICATED]);
    unset($_SESSION[self::SESSION_SETTINGS_USER]);
    $this->setMessage($I18n->translate('<p>Logged out from extendedWYSIWYG.</p>'), __METHOD__, __LINE__);
    return $this->actionLogin();
  } // actionLogout()

  /**
   * Action procedure for the Start dialog
   *
   * @return string
   */
  protected function actionStart() {
    global $I18n;

    $View = new viewSettings();
    $addonVersion = new addonVersion();
    $mysqlVersion = new mysqlVersion();
    $markdown = new Markdown();

    // read the changelog
    if (false === ($changelog = file_get_contents(CMS_ADDON_PATH.'/CHANGELOG'))) {
      $this->setMessage($I18n->translate('<p>Can\'t read the CHANGELOG!</p>'), __METHOD__, __LINE__);
      $changelog = '- error reading the CHANGELOG -';
    }

    // read the logfile
    $log = new logfile();
    $logfile = $log->getLog(true);

    $log_levels = array();
    $la = $log::$level_array;
    foreach ($la as $name => $value) {
      $log_levels[] = array(
          'name' => $name,
          'value' => $value
          );
    }

    $error_levels = array();
    $el = self::$ERROR_LEVELS;
    foreach ($el as $name => $value) {
      $error_levels[] = array(
          'name' => $name,
          'value' => $value
      );
    }
    $Settings = new LEPTON\Settings();
    $error_level = $Settings->select('er_level');

    $data = array(
        'template' => array(
            'url' => self::$TEMPLATE_URL
            ),
        'message' => array(
            'active' => (int) $this->isMessage(),
            'content' => $this->getMessage()
            ),
        'about' => array(
            'release' => $addonVersion->get(),
            'cms' => CMS_TYPE.' '.CMS_VERSION,
            'php' => PHP_VERSION,
            'mysql' => $mysqlVersion->get(),
            'changelog' => $markdown->parse($changelog)
            ),
        'logfile' => array(
            'active' => (int) !empty($logfile),
            'content' => $logfile,
            'level' => array(
                'value' => self::$LOGGER_LEVEL,
                'name' => self::REQUEST_LOGFILE_LEVEL,
                'options' => $log_levels
                )
            ),
        'error_level' => array(
            'value' => $error_level,
            'name' => self::REQUEST_ERROR_LEVEL,
            'options' => $error_levels
            ),
        'form' => array(
            'name' => 'dialog_start',
            'action' => self::$SETTINGS_URL
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_CHANGE_LEVEL
            )
        );
    return $View->dialogStart(self::ACTION_START, $data);
  } // actionStart()

  /**
   * Change the Logger level
   *
   * @return boolean|string
   */
  protected function actionChangeLevel() {
    global $I18n;
    global $logger;

    $messages = $this->getMessage();

    if (isset($_REQUEST[self::REQUEST_LOGFILE_LEVEL])) {
      $level = (int) $_REQUEST[self::REQUEST_LOGFILE_LEVEL];
      if ($level != CMS_LOGGER_LEVEL) {
        self::$LOGGER_LEVEL = $level;
        $logfile = new logfile();
        if (!$logfile->changeLoggerLevel($level)) {
          $this->setError($logfile->getError(), __METHOD__, __LINE__);
          return false;
        }
        $messages .= $I18n->translate('<p>The loglevel is successfull changed to {{ level }}</p>',
            array('level' => $logger->getLevelName($level)));
      }
    }
    if (isset($_REQUEST[self::REQUEST_ERROR_LEVEL])) {
      $error_level = (int) $_REQUEST[self::REQUEST_ERROR_LEVEL];
      $Settings = new LEPTON\Settings();
      $old_level = $Settings->select('er_level');
      if ($old_level != $error_level) {
        if (!$Settings->update('er_level', $error_level)) {
          $this->setError($Settings->getError(), __METHOD__, __LINE__);
          return false;
        }
        $messages .= $I18n->translate('<p>The error level is successfull changed to {{ level }}.</p>',
            array('level' => array_search($error_level, self::$ERROR_LEVELS)));
      }
    }
    $this->setMessage($messages, __METHOD__, __LINE__);
    return $this->actionStart();
  } // actionChangeLevel()

  /**
   * Check if the user is authenticated and has administrator rights, sets
   * the $_SESSION vars for the settings dialog
   *
   * @return boolean
   */
  public function checkLoginAsAdmin() {
    global $I18n;
    global $tools;
    if (!isset($_REQUEST[self::REQUEST_USERNAME]) || empty($_REQUEST[self::REQUEST_USERNAME]) ||
        !isset($_REQUEST[self::REQUEST_PASSWORD]) || empty($_REQUEST[self::REQUEST_PASSWORD])) {
      $this->setMessage($I18n->translate('<p>Please type in your username and password!</p>'), __METHOD__, __LINE__);
      return false;
    }
    $username = $tools->sanitizeVariable($_REQUEST[self::REQUEST_USERNAME]);
    $password = $tools->sanitizeVariable($_REQUEST[self::REQUEST_PASSWORD]);
    $Users = new LEPTON\Users();
    if ($Users->checkLogin($username, $password, true)) {
      $_SESSION[self::SESSION_SETTINGS_AUTHENTICATED] = true;
      $_SESSION[self::SESSION_SETTINGS_USER] = $username;
      return true;
    }
    unset($_SESSION[self::SESSION_SETTINGS_AUTHENTICATED]);
    unset($_SESSION[self::SESSION_SETTINGS_USER]);
    return false;
  } // checkLoginAsAdmin()

  /**
   * Action procedure for the editorial department
   *
   * @return boolean|string
   */
  protected function actionEditorialDepartment($department_id=-1) {
    global $I18n;

    // init configuration
    $config = new wysiwygConfiguration();
    // init the editorial departments
    $editorDepartment = new editorDepartment();
    // init pages access
    $Pages = new LEPTON\Pages();

    // get the list of all departments
    if (false === ($all_departments = $editorDepartment->selectAllDepartments())) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    $department_list = array();
    foreach ($all_departments as $department) {
      // create the department list
      if ($department['root_parent'] > 0) {
        if (false === ($page = $Pages->select($department['root_parent']))) {
          $this->setError($Pages->getError(), __METHOD__, __LINE__);
          return false;
        }
        if (count($page) < 1) {
          // the page does no longer exists!
          $message = $I18n->translate('<p>The page with the ID {{ id }} for the root parent of the department {{ name }} does no longer exists!</p><p>The department will be locked, please assign a new root parent!</p>',
              array('id' => $department['root_parent'], 'name' => $department['name']));
          $this->setMessage($message, __METHOD__, __LINE__);
          // log this as an error
          $this->setError($message, __METHOD__, __LINE__);
          // .. but don't prompt it! So, we reset:
          $this->resetError();
          $department['status'] = 'LOCKED';
          $department['root_parent'] = '-1';
          // update the record
          if (!$editorDepartment->update($department['id'], $department)) {
            $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
            return false;
          }
          // invalid page, set defaults
          $page = array(
              'page_title' => $I18n->translate('- not available -'),
              'menu_title' => $I18n->translate('- not available -')
          );
        }
      }
      elseif ($department['root_parent'] == 0) {
        // configured as root!
        $page = array(
            'page_title' => $I18n->translate('- Unique root page for all editors -'),
            'menu_title' => $I18n->translate('- Unique root page for all editors -')
        );
      }
      elseif ($department['root_parent'] < 0) {
        // invalid page, set defaults
        $page = array(
            'page_title' => $I18n->translate('- not available -'),
            'menu_title' => $I18n->translate('- not available -')
            );
      }

      $department_list[$department['id']] = array(
          'id' => $department['id'],
          'name' => $department['name'],
          'description' => $department['description'],
          'root_parent' => $department['root_parent'],
          'status' => $department['status'],
          'link' => sprintf('%s%s%s', self::$SETTINGS_URL,
              (false === strpos(self::$SETTINGS_URL, '?')) ? '?' : '&',
              http_build_query(array(
                  self::REQUEST_ACTION => self::ACTION_EDITORIAL,
                  self::REQUEST_SUB_ACTION => self::ACTION_EDITORIAL_DEPARTMENT,
                  self::REQUEST_SUB_SUB_ACTION => self::ACTION_EDIT_DEPARTMENT,
                  self::REQUEST_DEPARTMENT_ID => $department['id']
                  ))),
          'page' => array(
              'page_title' => $page['page_title'],
              'menu_title' => $page['menu_title'],
              'id' => $department['root_parent']
              )
          );
    } // foreach

    // is configured as single root department?
    if (false === ($root_id = $editorDepartment->getDepartmentRootId())) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    if ($root_id > 0) {
      // configured as single root department!
      $page_options = array();
    }
    else {
      // it may exists multiple departments
      $Pages = new LEPTON\Pages();
      // first fix the root_parent problem!
      if (!$Pages->fixRootParentProblem()) {
        $this->setError($Pages->getError(), __METHOD__, __LINE__);
        return false;
      }
      // get the maximum page levels
      $max_level = $config->getValue('cfgDepartmentMaxPageLevels');
      if ($config->isError()) {
        $this->setError($config->getError(), __METHOD__, __LINE__);
        return false;
      }
      if (false === ($pages_list = $editorDepartment->selectPagesList($max_level))) {
        $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
        return false;
      }
      $page_options = array();
      foreach ($pages_list as $page) {
        $spacer = '';
        if ($page['level'] > 0) {
          for ($i=0; $i< ($page['level']+1); $i++)
            $spacer .= '-';
          $spacer .= ' ';
        }
        $page_options[] = array(
            'value' => $page['page_id'],
            'text' => $spacer.$page['menu_title']
            );
      }
    }

    if ($department_id < 0) {
      // default record for insertation
      $department = array(
          'id' => -1,
          'root_parent' => -1,
          'name' => '',
          'description' => '',
          'status' => 'ACTIVE',
          'page' => array(
            'page_title' => $I18n->translate('- not available -'),
            'menu_title' => $I18n->translate('- not available -')
            )
          );
    }
    else {
      // select the department id for editing!
      $department = $department_list[$department_id];
    }

    $data = array(
        'page' => array(
            'name' => self::REQUEST_PAGE,
            'value' => -1,
            'options' => $page_options
            ),
        'form' => array(
            'name' => 'add_department',
            'action' => self::$SETTINGS_URL
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDITORIAL,
            ),
        'sub_action' => array(
            'name' => self::REQUEST_SUB_ACTION,
            'value' => self::ACTION_EDITORIAL_DEPARTMENT,
            ),
        'sub_sub_action' => array(
            'name' => self::REQUEST_SUB_SUB_ACTION,
            'value' => ($department_id < 1) ? self::ACTION_ADD_DEPARTMENT : self::ACTION_CHECK_DEPARTMENT
            ),
        'department' => array(
            'root_only' => ($root_id > 0) ? 1 : 0,
            'edit' => array(
                'id' => array(
                    'name' => self::REQUEST_DEPARTMENT_ID,
                    'value' => $department['id']
                    ),
                'name' => array(
                    'name' => self::REQUEST_DEPARTMENT,
                    'value' => $department['name']
                    ),
                'description' => array(
                    'name' => self::REQUEST_DEPARTMENT_DESCRIPTION,
                    'value' => $department['description']
                    ),
                'root_parent' => $department['root_parent'],
                'status' => array(
                    'name' => self::REQUEST_STATUS,
                    'value' => $department['status']
                    ),
                'page' => $department['page']
                ),
            'list' => array(
                'count' => count($department_list),
                'items' => $department_list
                )
            ),
        'message' => array(
            'active' => (int) $this->isMessage(),
            'content' => $this->getMessage()
            ),
        );
    $View = new viewSettings();
    return $View->dialogEditorialDepartment($data);
  } // actionEditorialDepartment()

  /**
   * Action procedure to add a new department
   *
   * @return Ambigous <boolean, string>|boolean
   */
  protected function actionAddDepartment() {
    global $I18n;

    // check the must fields
    if (!isset($_REQUEST[self::REQUEST_PAGE]) || ($_REQUEST[self::REQUEST_PAGE] < 0) ||
        !isset($_REQUEST[self::REQUEST_DEPARTMENT]) || empty($_REQUEST[self::REQUEST_DEPARTMENT])) {
      $this->setMessage($I18n->translate('<p>Please select a root page and a name for the new department!</p>'), __METHOD__, __LINE__);
      return $this->actionEditorialDepartment();
    }
    $data = array(
        'root_parent' => (int) $_REQUEST[self::REQUEST_PAGE],
        'name' => $_REQUEST[self::REQUEST_DEPARTMENT],
        'description' => (isset($_REQUEST[self::REQUEST_DEPARTMENT_DESCRIPTION])) ? trim($_REQUEST[self::REQUEST_DEPARTMENT_DESCRIPTION]) : ''
        );

    $editorDepartment = new editorDepartment();
    // insert the new department
    if (!$editorDepartment->insert($data)) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    $this->setMessage($I18n->translate('<p>The department {{ name }} was successfull inserted.</p>',
        array('name' => $data['name'])), __METHOD__, __LINE__);
    return $this->actionEditorialDepartment();
  } // actionAddDepartment()

  /**
   * Action procedure if a department is selected for editing
   *
   * @return boolean|Ambigous <boolean, string>
   */
  protected function actionEditDepartment() {
    global $I18n;

    if (!isset($_REQUEST[self::REQUEST_DEPARTMENT_ID]) || ($_REQUEST[self::REQUEST_DEPARTMENT_ID] < 1)) {
      $this->setError($I18n->translate('Got a invalid ID for the root parent!', __METHOD__, __LINE__));
      return false;
    }
    $department_id = (int) $_REQUEST[self::REQUEST_DEPARTMENT_ID];
    $this->setMessage($I18n->translate('<p>Please edit the department with the ID {{ id }}.</p>',
        array('id' => $department_id)), __METHOD__, __LINE__);
    return $this->actionEditorialDepartment($department_id);
  } // actionEditDepartment()

  /**
   * Action procedure to update a department record
   *
   * @return boolean|Ambigous <boolean, string>
   */
  protected function actionCheckDepartment() {
    global $I18n;

    if (!isset($_REQUEST[self::REQUEST_DEPARTMENT_ID]) || ($_REQUEST[self::REQUEST_DEPARTMENT_ID] < 1)) {
      $this->setError($I18n->translate('Got a invalid ID for the root parent!'), __METHOD__, __LINE__);
      return false;
    }

    // init department
    $editorDepartment = new editorDepartment();

    $department_id = (int) $_REQUEST[self::REQUEST_DEPARTMENT_ID];
    $status = $_REQUEST[self::REQUEST_STATUS];

    if ($status == 'DELETED') {
      // delete the department
      if (!$editorDepartment->delete($department_id)) {
        $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
        return false;
      }
      // set a message
      $this->setMessage($I18n->translate('<p>The department with the ID {{ id }} was deleted.</p>',
          array('id' => $department_id)), __METHOD__, __LINE__);
      // now we have to update the editors
      $editorTeam = new editorTeam();
      $Users = new LEPTON\Users();

      if (false === ($editors = $editorTeam->selectEditorsOfDepartment($department_id, false))) {
        $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }
      // loop through the editors and delete matching departments
      foreach ($editors as $editor_id) {
        if (false === ($editor = $editorTeam->selectEditorById($editor_id))) {
          $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
          return false;
        }
        $departments = explode(',', $editor['departments']);
        unset($departments[array_search($department_id, $departments)]);
        $data = array(
            'departments' => implode(',', $departments)
            );
        if (!$editorTeam->update($editor_id, $data)) {
          $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
          return false;
        }
        // set message
        $this->setMessage($I18n->translate('<p>Removed department id {{ id }} from the editor {{ name }}.</p>',
            array('id' => $department_id, 'name' => $Users->getUserDisplayName($editor['name']))), __METHOD__, __LINE__);
      }
      return $this->actionEditorialDepartment();
    }

    // update the department
    $description = $_REQUEST[self::REQUEST_DEPARTMENT_DESCRIPTION];
    $name = $_REQUEST[self::REQUEST_DEPARTMENT];
    $root_parent = (int) $_REQUEST[self::REQUEST_PAGE];

    $data = array(
        'name' => $name,
        'description' => $description,
        'root_parent' => $root_parent,
        'status' => $status
        );

    if (!$editorDepartment->update($department_id, $data)) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    $this->setMessage($I18n->translate('<p>Updated the department {{ name }}.</p>',
        array('name' => $data['name'])), __METHOD__, __LINE__);
    return $this->actionEditorialDepartment();
  } // actionCheckDepartment()

  /**
   * Action procedure for the editorial team
   *
   * @return boolean|string
   */
  protected function actionEditorialTeam($editor_id=-1) {
    global $I18n;

    $editorTeam = new editorTeam();

    // get all available CMS users
    if (false === ($all_users = $editorTeam->selectAsEditorAvailableUsers())) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    $options_user = array();
    foreach ($all_users as $user) {
      // skip inactive users
      $options_user[] = array(
          'value' => $user['username'],
          'text' => (!empty($user['display_name'])) ? $user['display_name'] : $user['username']
          );
    }

    // init the user access
    $Users = new LEPTON\Users();
    // init the pages access
    $Pages = new LEPTON\Pages();
    // init the department access
    $editorDepartment = new editorDepartment();

    // get the list with the editors
    if (false === ($editors = $editorTeam->selectAllEditors())) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    $editors_array = array();
    foreach ($editors as $editor) {
      // get the display name
      $display_name = $Users->getUserDisplayName($editor['name']);
      // explode all departments
      $departments = explode(',', $editor['departments']);
      $departments_array = array();
      // get the name for each department
      foreach ($departments as $department_id) {
        if (empty($department_id)) continue;
        if (false === ($department = $editorDepartment->select($department_id))) {
          $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
          return false;
        }
        $departments_array[] = $department['name'];
      } // foreach

      $editors_array[$editor['id']] = array(
          'id' => $editor['id'],
          'name' => $editor['name'],
          'display_name' => (!empty($display_name)) ? $display_name : $editor['name'],
          'email' => $Users->getUserEMail($editor['name']),
          'position' => $editor['position'],
          'departments' => $departments_array,
          'permissions' => $editor['permissions'],
          'last_activity' => $editor['last_activity'],
          'status' => $editor['status'],
          'timestamp' => $editor['timestamp'],
          'link' => sprintf('%s%s%s', self::$SETTINGS_URL,
              (false === strpos(self::$SETTINGS_URL, '?')) ? '?' : '&',
              http_build_query(array(
                  self::REQUEST_ACTION => self::ACTION_EDITORIAL,
                  self::REQUEST_SUB_ACTION => self::ACTION_EDITORIAL_TEAM,
                  self::REQUEST_SUB_SUB_ACTION => self::ACTION_EDIT_EDITOR,
                  self::REQUEST_EDITOR_ID => $editor['id']
                  ))),
          'supervisors' => explode(',', $editor['supervisors'])
          );
    } // foreach editors


    $editor = array();
    if ($editor_id > -1) {
      // edit the editor ;-)
      $source = $editors_array[$editor_id];
      // build the positions array
      $positions_array = array(
          'CHIEF_EDITOR' => 'CHIEF_EDITOR',
          'SUB_CHIEF_EDITOR' => 'SUB_CHIEF_EDITOR',
          'EDITOR' => 'EDITOR',
          'TRAINEE' => 'TRAINEE'
      );
      // remove position which are no longer available!
      if ($editorTeam->existsPosition('CHIEF_EDITOR') && ($source['position'] != 'CHIEF_EDITOR'))
        unset($positions_array['CHIEF_EDITOR']);
      if ($editorTeam->existsPosition('SUB_CHIEF_EDITOR') && ($source['position'] != 'SUB_CHIEF_EDITOR'))
        unset($positions_array['SUB_CHIEF_EDITOR']);

      // build the department array
      if (false === ($departments = $editorDepartment->selectAllDepartments(true))) {
        $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
        return false;
      }
      $department_array = array();
      foreach ($departments as $department) {
        $department_array[] = array(
            'value' => $department['id'],
            'text' => $department['name'],
            'checked' => (int) in_array($department['name'], $source['departments'])
            );
      }

      // get the supervisors
      if (false === ($supervisors = $editorTeam->selectSupervisors())) {
        $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }
      $supervisor_array = array();
      foreach ($supervisors as $supervisor) {
        if ($supervisor['username'] == $source['name']) continue;
        $supervisor_array[] = array(
            'value' => $supervisor['username'],
            'text' => (!empty($supervisor['display_name'])) ? $supervisor['display_name'] : $supervisor['username'],
            'checked' => (int) in_array($supervisor['username'], $source['supervisors'])
            );
      }

      $position_permissions = array();
      $pp = editorTeam::$position_permissions;
      foreach ($pp as $permission => $text) {
        $position_permissions[] = array(
            'value' => $permission,
            'text' => $text,
            'checked' => (int) $editorTeam->checkPermission($source['permissions'], $permission)
            );
      }

      $section_permissions = array();
      $sp = editorTeam::$section_permissions;
      foreach ($sp as $permission => $text) {
        $section_permissions[] = array(
            'value' => $permission,
            'text' => $text,
            'checked' => (int) $editorTeam->checkPermission($source['permissions'], $permission)
        );
      }

      $release_permissions = array();
      $rp = editorTeam::$release_permissions;
      foreach ($rp as $permission => $text) {
        $release_permissions[] = array(
            'value' => $permission,
            'text' => $text,
            'checked' => (int) $editorTeam->checkPermission($source['permissions'], $permission)
        );
      }

      $editor = array(
          'id' => array(
              'name' => self::REQUEST_EDITOR_ID,
              'value' => $editor_id
              ),
          'name' => array(
              'value' => $source['name'],
              'name' => self::REQUEST_USERNAME
              ),
          'display_name' => $source['display_name'],
          'position' => array(
              'name' => self::REQUEST_EDITOR_POSITION,
              'value' => $source['position'],
              'options' => $positions_array
              ),
          'department' => array(
              'name' => self::REQUEST_DEPARTMENT,
              'list' => $department_array
              ),
          'status' => array(
              'name' => self::REQUEST_STATUS,
              'value' => $source['status']
              ),
          'supervisors' => array(
              'name' => self::REQUEST_SUPERVISORS,
              'list' => $supervisor_array
              ),
          'permissions' => array(
              'name' => self::REQUEST_PERMISSIONS,
              'position' => $position_permissions,
              'section' => $section_permissions,
              'release' => $release_permissions
              )

          );
    }

    $chief_editor = $editorTeam->selectChiefEditorName();

    $data = array(
        'form' => array(
            'name' => 'editorial_team',
            'action' => self::$SETTINGS_URL
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDITORIAL,
            ),
        'sub_action' => array(
            'name' => self::REQUEST_SUB_ACTION,
            'value' => self::ACTION_EDITORIAL_TEAM,
            ),
        'sub_sub_action' => array(
            'name' => self::REQUEST_SUB_SUB_ACTION,
            'value' => ($editor_id < 1) ? self::ACTION_ADD_EDITOR : self::ACTION_CHECK_EDITOR
            ),
        'users' => array(
            'name' => self::REQUEST_USERNAME,
            'options' => $options_user
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDITORIAL
            ),
        'sub_action' => array(
            'name' => self::REQUEST_SUB_ACTION,
            'value' => self::ACTION_EDITORIAL_TEAM
            ),
        'editor' => array(
            'chief_editor' => array(
                'exists' => ($chief_editor != '') ? 1 : 0
                ),
            'list' => array(
                'count' => count($editors_array),
                'items' => $editors_array
                ),
            'id' => array(
                'name' => self::REQUEST_EDITOR_ID,
                'value' => $editor_id
                ),
            'edit' => $editor
            ),
        'message' => array(
            'active' => (int) $this->isMessage(),
            'content' => $this->getMessage()
            ),
    );
    $View = new viewSettings();
    return $View->dialogEditorialTeam(self::ACTION_EDITORIAL, self::ACTION_EDITORIAL_TEAM, $data);
  } // actionEditorialTeam()

  /**
   * Action procedure to start editing an editor
   *
   * @return boolean|Ambigous <boolean, string>
   */
  protected function actionEditEditor() {
    global $I18n;

    if (!isset($_REQUEST[self::REQUEST_EDITOR_ID]) || ($_REQUEST[self::REQUEST_EDITOR_ID] < 1)) {
      $this->setError($I18n->translate('Got a invalid ID for the editor!', __METHOD__, __LINE__));
      return false;
    }
    $editor_id = (int) $_REQUEST[self::REQUEST_EDITOR_ID];
    $this->setMessage($I18n->translate('<p>Please edit the editor with the ID {{ id }}.</p>',
        array('id' => $editor_id)), __METHOD__, __LINE__);
    return $this->actionEditorialTeam($editor_id);
  } // actionEditEditor()

  /**
   * Action procedure to insert a new editor
   *
   * @return boolean|Ambigous <boolean, string>
   */
  protected function actionAddEditor() {
    global $I18n;

    if (!isset($_REQUEST[self::REQUEST_USERNAME]) || empty($_REQUEST[self::REQUEST_USERNAME])) {
      $this->setError($I18n->translate('Got a invalid ID for the editor!'), __METHOD__, __LINE__);
      return false;
    }
    $username = $_REQUEST[self::REQUEST_USERNAME];

    // ok - now we insert a new editor record with defaults
    $editorTeam = new editorTeam();
    if ($editorTeam->selectChiefEditorName() == '') {
      // it exists no chief editor!
      $data = array(
          'name' => $username,
          'status' => 'ACTIVE',
          'position' => 'CHIEF_EDITOR',
          'permissions' => editorTeam::DEFAULT_PERMISSION_CHIEF_EDITOR,
          'supervisors' => ''
      );
    }
    else {
      // default settings for a editor
      $data = array(
          'name' => $username,
          'status' => 'LOCKED',
          'position' => 'EDITOR',
          'permissions' => editorTeam::DEFAULT_PERMISSION_EDITOR,
          'supervisors' => $editorTeam->selectChiefEditorName()
          );
    }
    $editor_id = -1;
    if (!$editorTeam->insert($data, $editor_id)) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    $this->setMessage($I18n->translate('<p>Please edit the editor with the ID {{ id }}.</p>',
        array('id' => $editor_id)), __METHOD__, __LINE__);
    return $this->actionEditorialTeam($editor_id);
  } // actionAddEditor()

  /**
   * Action procedure for the update check of an editor
   *
   * @return boolean|Ambigous <boolean, string>|string
   */
  protected function actionCheckEditor() {
    global $I18n;

     if (!isset($_REQUEST[self::REQUEST_STATUS]) || !isset($_REQUEST[self::REQUEST_USERNAME]) ||
         !isset($_REQUEST[self::REQUEST_EDITOR_POSITION]) || !isset($_REQUEST[self::REQUEST_PERMISSIONS]) ||
         !is_array($_REQUEST[self::REQUEST_PERMISSIONS]) || !isset($_REQUEST[self::REQUEST_EDITOR_ID])) {
      // missing one or more fields!
      $this->setError($I18n->translate('<p>Missing one or more fields!</p>'), __METHOD__, __LINE__);
      return false;
    }

    // init the editorTeam
    $editorTeam = new editorTeam();

    // init the User access
    $Users = new LEPTON\Users();

    $status = $_REQUEST[self::REQUEST_STATUS];
    $name = $_REQUEST[self::REQUEST_USERNAME];
    $position = $_REQUEST[self::REQUEST_EDITOR_POSITION];
    $permissions = array_sum($_REQUEST[self::REQUEST_PERMISSIONS]);
    $editor_id = (int) $_REQUEST[self::REQUEST_EDITOR_ID];

    if ($status == 'DELETED') {
      // delete the editor
      if (!$editorTeam->deleteByName($name)) {
        $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
        return false;
      }
      $this->setMessage($I18n->translate('<p>Successfull deleted the editor with the name {{ name }}.</p>',
          array('name' => $Users->getUserDisplayName($name))), __METHOD__, __LINE__);
      return $this->actionEditorialTeam();
    }

    // check the supervisors
    if (!isset($_REQUEST[self::REQUEST_SUPERVISORS])) {
      $supervisors = '';
      // no supervisor assigned!
      if ($position == 'TRAINEE') {
        // Trainee without supervisor!
        $this->setMessage($I18n->translate('<p>Each Trainee must have one or more Supervisors, please check your settings!</p>'),
            __METHOD__, __LINE__);
        return $this->actionEditorialTeam($editor_id);
      }
      if (!$editorTeam->checkPermission($permissions, editorTeam::PERMISSION_RELEASE_BY_OWN)) {
        // need release by own or one or more supervisors!
        $this->setMessage($I18n->translate('<p>The editor must have the permission to release articles by his own or you must assign one or more supervisors to the editor!</p>'),
            __METHOD__, __LINE__);
        return $this->actionEditorialTeam($editor_id);
      }
    }
    elseif (!is_array($_REQUEST[self::REQUEST_SUPERVISORS])) {
      $this->setError($I18n->translate('The supervisors field must be of type array!'), __METHOD__, __LINE__);
      return false;
    }
    else {
      $supervisors = implode(',', $_REQUEST[self::REQUEST_SUPERVISORS]);
    }

    // check the departments
    if (!isset($_REQUEST[self::REQUEST_DEPARTMENT])) {
      // no department assigned - prompt a message but don't break the process
      $this->setMessage($I18n->translate('<p>The editor {{ name }} is not assigned to a department, is this correct?</p>',
          array('name' => $Users->getUserDisplayName($name))), __METHOD__, __LINE__);
      $departments = '';
    }
    elseif (!is_array($_REQUEST[self::REQUEST_DEPARTMENT])) {
      $this->setError($I18n->translate('The departments field must be of type array!'), __METHOD__, __LINE__);
      return false;
    }
    else {
      $departments = implode(',', $_REQUEST[self::REQUEST_DEPARTMENT]);
    }

    // check the position!
    switch ($position):
    case 'CHIEF_EDITOR':
      $perm = editorTeam::PERMISSION_POSITION_CHIEF_EDITOR; break;
    case 'SUB_CHIEF_EDITOR':
      $perm = editorTeam::PERMISSION_POSITION_SUB_CHIEF_EDITOR; break;
    case 'EDITOR':
      $perm = editorTeam::PERMISSION_POSITION_EDITOR; break;
    case 'TRAINEE':
      $perm = editorTeam::PERMISSION_POSITION_TRAINEE; break;
    default:
      // position does not exists?!
      $this->setError($I18n->translate('The {{ position }} is unknown!',
        array('position' => $position)), __METHOD__, __LINE__);
      return false;
    endswitch;

    if (!$editorTeam->checkPermission($permissions, $perm)) {
      // the position and the permissions differ!
      if ($editorTeam->checkPermission($permissions, editorTeam::PERMISSION_POSITION_CHIEF_EDITOR))
        $permissions -= editorTeam::PERMISSION_POSITION_CHIEF_EDITOR;
      if ($editorTeam->checkPermission($permissions, editorTeam::PERMISSION_POSITION_SUB_CHIEF_EDITOR))
        $permissions -= editorTeam::PERMISSION_POSITION_SUB_CHIEF_EDITOR;
      if ($editorTeam->checkPermission($permissions, editorTeam::PERMISSION_POSITION_EDITOR))
        $permissions -= editorTeam::PERMISSION_POSITION_EDITOR;
      if ($editorTeam->checkPermission($permissions, editorTeam::PERMISSION_POSITION_TRAINEE))
        $permissions -= editorTeam::PERMISSION_POSITION_TRAINEE;
      $permissions += $perm;
    }

    // gather the data
    $data = array(
        'name' => $name,
        'position' => $position,
        'supervisors' => $supervisors,
        'departments' => $departments,
        'permissions' => $permissions,
        'status' => $status
        );

    if (!$editorTeam->update($editor_id, $data)) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    // prompt a success message
    $this->setMessage($I18n->translate('<p>The editor {{ name }} was successfull updated.</p>',
        array('name' => $Users->getUserDisplayName($name))), __METHOD__, __LINE__);
    return $this->actionEditorialTeam();
  } // actionCheckEditor()

} // class controlSettings