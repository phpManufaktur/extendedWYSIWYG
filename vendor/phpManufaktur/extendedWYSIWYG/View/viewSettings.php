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
global $tools;
global $cms;

$Settings = new viewSettings();
$Settings->action();

class viewSettings extends boneClass {

  const REQUEST_ACTION = 'act';

  const ACTION_DEFAULT = 'def';
  const ACTION_LOGIN = 'lgi';
  const ACTION_SETTINGS = 'set';

  protected static $TEMPLATE_PATH = null;
  protected static $IS_AUTHENTICATED = false;
  protected static $SETTINGS_URL = null;

  public function __construct() {
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/';
    self::$SETTINGS_URL = CMS_ADDON_URL.'/settings.php';
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

  public function action() {
    if (!self::$IS_AUTHENTICATED) {
      $_REQUEST[self::REQUEST_ACTION] = self::ACTION_SETTINGS;
    }

    $action = (isset($_REQUEST[self::REQUEST_ACTION])) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;

    switch ($action):
    case self::ACTION_SETTINGS:
      $result = $this->show(self::ACTION_SETTINGS, $this->dialogSetttings());
      break;
    case self::ACTION_LOGIN:
      $result = $this->show(self::ACTION_LOGIN, $this->dialogLogin());
      break;
    default:
       $result = $this->show(self::ACTION_DEFAULT, '- not defined -');
       break;
    endswitch;

    // prompt the complete settings dialog
    return $result;
  } // action()

  protected function show($action, $content) {
    $data = array(
        'title' => 'extendedWYSIWYG Settings',
        'css_url' => CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/screen.css',
        'css_config_url' => CMS_URL.'/modules/manufaktur_config/backend.css',
        'navigation' => '',
        'is_error' => $this->isError() ? 1 : 0,
        'content' => $this->isError() ? $this->getError() : $content
    );
    return $this->getTemplate('settings.page.dwoo', $data);
  } // show()

  protected function dialogLogin() {
    return __METHOD__;
  } // dialotLogin()

  protected function dialogSetttings() {
    // set the link to call the dlgConfig()
    $link = self::$SETTINGS_URL;
    // set the abort link (to modify page)
    $abort = self::$SETTINGS_URL;
    // exec manufakturConfig
    $dialog = new \manufakturConfigDialog('wysiwyg', 'extendedWYSIWYG', $link, $abort);
    return $dialog->action();
  } // dialogSettings()

} // class viewSettings

/*
// set the link to call the dlgConfig()
$link = CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/viewSettings.php';
// set the abort link (to modify page)
$abort = CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/View/viewSettings.php';
// exec manufakturConfig
$dialog = new \manufakturConfigDialog('wysiwyg', 'extendedWYSIWYG', $link, $abort);
echo $dialog->action();
*/