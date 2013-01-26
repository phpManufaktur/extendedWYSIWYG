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
      $result = $View->dialogSetttings($action, array());
      break;
    case self::ACTION_LOGIN:
      // user mus login as administrator!
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
        $result = $this->actionEditorialDepartment();
        break;
      case self::ACTION_EDITORIAL_TEAM:
      default:
        $result = $this->actionEditorialTeam();
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
    $View = new viewSettings();
    $data = array(
        'username' => isset($_REQUEST[self::REQUEST_USERNAME]) ? $_REQUEST[self::REQUEST_USERNAME] : '',
        'message' => $this->getMessage()
    );
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
      return $this->actionStart();
    }
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
    $View = new viewSettings();
    $data = array(
        'username' => '',
        'message' => $this->getMessage()
        );
    return $View->dialogLogin(self::ACTION_START, $data);
  } // actionLogout()

  /**
   * Action procedure for the Start dialog
   *
   * @return string
   */
  protected function actionStart() {
    global $db;
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
        'message' => $this->getMessage(),
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
  protected function actionEditorialDepartment() {
    // first fix the root_parent problem!
    $Pages = new LEPTON\Pages();
    if (!$Pages->fixRootParentProblem()) {
      $this->setError($Pages->getError(), __METHOD__, __LINE__);
      return false;
    }

    $editorDepartment = new editorDepartment();
    if (false === ($pages_list = $editorDepartment->selectPagesList(1))) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }
    echo "<pre>";
    print_r($pages_list);
    echo "</pre>";

    $data = array(
        );
    $View = new viewSettings();
    return $View->dialogEditorialDepartment(self::ACTION_EDITORIAL, self::ACTION_EDITORIAL_DEPARTMENT, $data);
  } // actionEditorialDepartment()

  /**
   * Action procedure for the editorial team
   *
   * @return boolean|string
   */
  protected function actionEditorialTeam() {
    $editorTeam = new editorTeam();
    if (false === ($all_users = $editorTeam->selectAsEditorAvailableUsers())) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }

    $options_user = array();
    foreach ($all_users as $user) {
      // skip inactive users
      if ($user['active'] != 1) continue;
      $options_user[] = array(
          'value' => $user['username'],
          'text' => $user['display_name']
          );
    }

    $data = array(
        'users' => array(
            'name' => self::REQUEST_USER,
            'options' => $options_user
            )
    );
    $View = new viewSettings();
    return $View->dialogEditorialTeam(self::ACTION_EDITORIAL, self::ACTION_EDITORIAL_TEAM, $data);
  } // actionEditorialTeam()

} // class controlSettings