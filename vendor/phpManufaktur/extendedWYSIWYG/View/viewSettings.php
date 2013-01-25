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

use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;


require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';
require_once CMS_PATH.'/modules/manufaktur_config/class.dialog.php';

global $I18n;
global $logger;
global $dwoo;

class viewSettings extends boneClass {

  const REQUEST_ACTION = 'act';
  const REQUEST_USERNAME = 'usr';
  const REQUEST_PASSWORD = 'pwd';

  const ACTION_DEFAULT = 'def';
  const ACTION_LOGIN = 'lgi';
  const ACTION_LOGIN_CHECK = 'lgic';
  const ACTION_LOGOUT = 'ext';
  const ACTION_SETTINGS = 'set';
  const ACTION_START = 'sta';

  protected static $TEMPLATE_PATH = null;
  protected static $TEMPLATE_URL = null;
  protected static $SETTINGS_URL = null;

  /**
   * Constructor for class viewSettings
   */
  public function __construct() {
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/';
    self::$TEMPLATE_URL = CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend';
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
   */
  protected function show($action, $content) {
    $tab_navigation_array = array(
        self::ACTION_START => 'extendedWYSIWYG',
        self::ACTION_SETTINGS => 'Settings',
        self::ACTION_LOGOUT => 'Logout'
    );

    $navigation = array();
    foreach ($tab_navigation_array as $key => $value) {
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
        'css_url' => CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/screen.css',
        'css_config_url' => CMS_URL.'/modules/manufaktur_config/backend.css',
        'navigation' => $navigation,
        'is_error' => $this->isError() ? 1 : 0,
        'content' => $this->isError() ? $this->getError() : $content
    );
    return $this->getTemplate('settings.page.dwoo', $data);
  } // show()

  /**
   * Shows the login dialog
   *
   * @param string $action
   * @param array $data
   */
  public function dialogLogin($action, $data) {
    $template = array(
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
    $dialog = $this->getTemplate('settings.login.dwoo', $template);
    return $this->show($action, $dialog);
  } // dialogLogin()

  /**
   * Show the setting dialog for the general options
   *
   * @param string $action
   * @param string $data
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
   */
  public function dialogStart($action, $data) {
    $template = array(
        'template' => array(
            'url' => self::$TEMPLATE_URL
            ),
        'about' => $data['about'],
        );
    $dialog = $this->getTemplate('settings.start.dwoo', $template);
    return $this->show($action,$dialog);
  } // dialogStart()

} // class viewSettings

