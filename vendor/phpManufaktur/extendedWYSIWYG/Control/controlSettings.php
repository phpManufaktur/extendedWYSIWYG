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

use Markdown\Markdown;

use phpManufaktur\CMS\Bridge\Data\mysqlVersion;
use phpManufaktur\extendedWYSIWYG\Data\addonVersion;
use phpManufaktur\CMS\Bridge\Data\LEPTON\Users;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\Data\LEPTON;
use phpManufaktur\extendedWYSIWYG\View\viewSettings;

class controlSettings extends boneClass {

  const REQUEST_ACTION = 'act';
  const REQUEST_USERNAME = 'usr';
  const REQUEST_PASSWORD = 'pwd';

  const ACTION_DEFAULT = 'def';
  const ACTION_LOGIN = 'lgi';
  const ACTION_LOGIN_CHECK = 'lgic';
  const ACTION_LOGOUT = 'ext';
  const ACTION_SETTINGS = 'set';
  const ACTION_START = 'sta';

  const SESSION_SETTINGS_AUTHENTICATED = 'ssa';
  const SESSION_SETTINGS_USER = 'ssu';

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
    case self::ACTION_START:
    default:
       $result = $this->actionStart();
       break;
    endswitch;

    // prompt the complete settings page
    return $result;
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

    if (false === ($changelog = file_get_contents(CMS_ADDON_PATH.'/CHANGELOG'))) {
      $this->setMessage($I18n->translate('<p>Can\t read the CHANGELOG!</p>'), __METHOD__, __LINE__);
      $changelog = '- error reading the CHANGELOG -';
    }

    $data = array(
        'message' => $this->getMessage(),
        'about' => array(
            'release' => $addonVersion->get(),
            'cms' => CMS_TYPE.' '.CMS_VERSION,
            'php' => PHP_VERSION,
            'mysql' => $mysqlVersion->get(),
            'changelog' => $markdown->parse($changelog)
            ),
        );
    return $View->dialogStart(self::ACTION_START, $data);
  } // actionStart()

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

} // class controlSettings