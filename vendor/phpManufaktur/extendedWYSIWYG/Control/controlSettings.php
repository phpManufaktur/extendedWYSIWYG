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
    foreach ($log::$level_array as $name => $value) {
      $log_levels[] = array(
          'name' => $name,
          'value' => $value
          );
    }

    $error_levels = array();
    foreach (self::$ERROR_LEVELS as $name => $value) {
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
    $department_id = (int) $_REQUEST[self::REQUEST_DEPARTMENT_ID];
    $data = array(
        'name' => $_REQUEST[self::REQUEST_DEPARTMENT],
        'description' => $_REQUEST[self::REQUEST_DEPARTMENT_DESCRIPTION],
        'root_parent' => (int) $_REQUEST[self::REQUEST_PAGE],
        'status' => $_REQUEST[self::REQUEST_STATUS]
        );
    $editorDepartment = new editorDepartment();
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
          'text' => $user['display_name']
          );
    }

    // init the user access
    $Users = new LEPTON\Users();

    // get the list with the editors
    if (false === ($editors = $editorTeam->selectAllEditors())) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }
    $editors_array = array();
    foreach ($editors as $editor) {
      $editors_array[$editor['id']] = array(
          'id' => $editor['id'],
          'name' => $editor['name'],
          'display_name' => $Users->getUserDisplayName($editor['name']),
          'email' => $Users->getUserEMail($editor['name']),
          'position' => $editor['position'],
          'departments' => explode(',', $editor['departments']),
          'rights' => $editor['rights'],
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
          );
    }


 /*
    $editorDepartment = new editorDepartment();
    if (false === ($departments = $editorDepartment->selectAllDepartments(true))) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    $department_array = array();
    foreach ($departments as $department)
*/
    $data = array(
        'users' => array(
            'name' => self::REQUEST_USER,
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
            'list' => array(
                'count' => count($editors_array),
                'items' => $editors_array
                ),
            'edit' => array(
                'id' => array(
                    'name' => self::REQUEST_EDITOR_ID,
                    'value' => $editor_id
                    ),
                ),
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

} // class controlSettings