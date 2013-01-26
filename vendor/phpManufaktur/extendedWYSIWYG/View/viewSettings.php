<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\View;

use phpManufaktur\extendedWYSIWYG\Control\boneSettings;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';
require_once CMS_PATH.'/modules/manufaktur_config/class.dialog.php';

global $I18n;
global $logger;
global $dwoo;

class viewSettings extends boneSettings {

  /**
   * Constructor for class viewSettings
   */
  public function __construct() {
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/settings/';
    self::$TEMPLATE_URL = CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/settings';
    self::$SETTINGS_URL = CMS_ADDON_URL.'/service.php';
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
      $this->setError($I18n->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()


  /**
   * Parse the final HTML page and return it
   *
   * @param string $action
   * @param string $content
   * @return string the complete settings page
   */
  public function show($action, $content) {
    $tab_navigation_array = array(
        self::ACTION_START => 'extendedWYSIWYG',
        self::ACTION_EDITORIAL => 'Editorial department',
        self::ACTION_SETTINGS => 'Settings',
        self::ACTION_LOGOUT => 'Logout'
    );

    $configuration = new wysiwygConfiguration();
    $useEditorial = $configuration->getValue('cfgUseEditorialDepartment');

    $navigation = array();
    foreach ($tab_navigation_array as $key => $value) {
      // don't show the tab if the editorial department is inactive!
      if (($key == self::ACTION_EDITORIAL) && !$useEditorial)
        continue;
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s%s%s', self::$SETTINGS_URL,
              (false === strpos(self::$SETTINGS_URL, '?')) ? '?' : '&',
              http_build_query(array(self::REQUEST_ACTION => $key))),
          'text' => $value
      );
    }

    $data = array(
        'title' => 'extendedWYSIWYG Settings',
        'css_url' => self::$TEMPLATE_URL.'/settings.css',
        'css_config_url' => CMS_URL.'/modules/manufaktur_config/backend.css',
        'navigation' => $navigation,
        'is_error' => $this->isError() ? 1 : 0,
        'content' => $this->isError() ? $this->getError() : $content
    );
    return $this->getTemplate('page.dwoo', $data);
  } // show()

  /**
   * Shows the login dialog
   *
   * @param string $action
   * @param array $data
   * @return string login dialog
   */
  public function dialogLogin($action, $data) {
    $template_data = array(
        'form' => array(
            'name' => 'login_dialog',
            'action' => self::$SETTINGS_URL
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_LOGIN_CHECK
            ),
        'message' => array(
            'active' => (int) (isset($data['message']) && !empty($data['message'])),
            'content' => (isset($data['message'])) ? $data['message'] : ''
            ),
        'login' => array(
            'username' => array(
                'name' => self::REQUEST_USERNAME,
                'value' => $data['username']
                ),
            'password' => array(
                'name' => self::REQUEST_PASSWORD,
                'value' => ''
                ),
            ),
        );
    $dialog = $this->getTemplate('login.dwoo', $template_data);
    return $this->show($action, $dialog);
  } // dialogLogin()

  /**
   * Show the setting dialog for the general options
   *
   * @param string $action
   * @param string $data
   * @return string configuration dialog
   */
  public function dialogSetttings($action, $data) {
    // set the link to call the dlgConfig()
    $link = sprintf('%s%s%s', self::$SETTINGS_URL,
        (false === strpos(self::$SETTINGS_URL, '?')) ? '?' : '&',
        http_build_query(array(self::REQUEST_ACTION => self::ACTION_SETTINGS)));
    // set the abort link (to modify page)
    $abort = self::$SETTINGS_URL;
    // exec manufakturConfig
    $dialog = new \manufakturConfigDialog('wysiwyg', 'extendedWYSIWYG', $link, $abort);
    $content = $dialog->action();
    return $this->show($action, $content);
  } // dialogSettings()

  /**
   * Show the start dialog with addon information, changelog, actual log a.s.o.
   *
   * @param string $action
   * @param array $data
   * @return string dialog
   */
  public function dialogStart($action, $data) {
    $template_data = array(
        'template' => array(
            'url' => self::$TEMPLATE_URL
            ),
        'about' => $data['about'],
        'logfile' => $data['logfile'],
        'form' => array(
            'name' => 'dialog_start',
            'action' => self::$SETTINGS_URL
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_CHANGE_LEVEL
            ),
        'message' => array(
            'active' => (int) !empty($data['message']),
            'content' => $data['message']
            ),
        'error_level' => $data['error_level']
        );
    $dialog = $this->getTemplate('start.dwoo', $template_data);
    return $this->show($action, $dialog);
  } // dialogStart()

  /**
   * Create the subnavigation for the editorial department
   *
   * @param string $action
   * @return string
   */
  protected function getEditorialNavigation($action) {
    $tab_navigation_array = array(
        self::ACTION_EDITORIAL_TEAM => 'Editorial team',
        self::ACTION_EDITORIAL_DEPARTMENT => 'Departments',
    );
    $navigation = array();
    foreach ($tab_navigation_array as $key => $value) {
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s%s%s', self::$SETTINGS_URL,
              (false === strpos(self::$SETTINGS_URL, '?')) ? '?' : '&',
              http_build_query(array(
                  self::REQUEST_ACTION => self::ACTION_EDITORIAL,
                  self::REQUEST_SUB_ACTION => $key
                  ))),
          'text' => $value
      );
    }
    return $navigation;
  } // getEditorialNavigation()

  /**
   * Dialog for the editorial team
   *
   * @param string $action
   * @param array $data
   * @return string dialog
   */
  public function dialogEditorialTeam($action, $sub_action, $data) {
    $template_data = array(
        'sub_navigation' => $this->getEditorialNavigation($sub_action),
        'users' => $data['users'],

        );
    $dialog = $this->getTemplate('editorial.team.dwoo', $template_data);
    return $this->show($action, $dialog);
  } // dialogEditorialTeam()

  /**
   * Dialog for the editorial department
   *
   * @param string $action
   * @param array $data
   * @return string dialog
   */
  public function dialogEditorialDepartment($action, $sub_action, $data) {
    $template_data = array(
        'sub_navigation' => $this->getEditorialNavigation($sub_action)
    );
    $dialog = $this->getTemplate('editorial.department.dwoo', $template_data);
    return $this->show($action, $dialog);
  } // dialogEditorialDepartment()

} // class viewSettings

