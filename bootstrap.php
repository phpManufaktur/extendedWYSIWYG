<?php

use phpManufaktur\extendedWYSIWYG\Control\controlSettings;

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

include_once __DIR__.'/vendor/Autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use phpManufaktur\CMS\Bridge\cmsBridge;
use phpManufaktur\Toolbox\Control\Toolbox;
use phpManufaktur\extendedWYSIWYG\View\viewSettings;

global $cms;
global $logger;
global $db;
global $tools;

if (is_object($cms) && ($cms->getStatus() == cmsBridge::STATUS_INITIALIZED)) {
  // the cmsBrigde is already initialized
  $logger->addInfo('The cmsBridge is already initialized, skip bootstrap routines.');
}
else {
  // initialize the cmsBridge

  // set the default timezone
  date_default_timezone_set('Europe/Berlin');

  // initialize Toolbox
  $tools = new Toolbox();

  if (!defined('CMS_ADDON_PATH'))
    define('CMS_ADDON_PATH', $tools->sanitizePath(__DIR__));

  if (!defined('CMS_ADDON_CONFIG_PATH'))
    define('CMS_ADDON_CONFIG_PATH', $tools->sanitizePath(__DIR__.'/config'));

  // check if a configuration file for the addon exists
  if (file_exists(CMS_ADDON_CONFIG_PATH.'/addonConfig.json')) {
    $addonConfig = json_decode(file_get_contents(CMS_ADDON_CONFIG_PATH.'/addonConfig.json'), true);
    if (isset($addonConfig['logger']['level'])) {
      // get the logger level from the config file
      $logger_level = $addonConfig['logger']['level'];
    }
  }
  else {
    // default level for the logger
    $logger_level = Logger::ERROR;
  }
  define('CMS_LOGGER_LEVEL', $logger_level);

  // check the logfile size
  $max_size = 2*1024*1024; // 2 MB
  $log_file = $tools->sanitizePath(__DIR__.'/logfile/extendedWYSIWYG.log');
  if (file_exists($log_file) && (filesize($log_file) > $max_size)) {
    @unlink($tools->sanitizePath(__DIR__.'/logfile/extendedWYSIWYG.bak'));
    @rename($log_file, $tools->sanitizePath(__DIR__.'/logfile/extendedWYSIWYG.bak'));
  }
  // initialize the logger
  $logger = new Logger('extendedWYSIWYG');
  $logger->pushHandler(new StreamHandler($log_file, CMS_LOGGER_LEVEL));
  $logger->addInfo('Monolog initialized');

  // the cmsBrige must prepared before it could initialized
  $cms = new cmsBridge($logger);
  if (!$cms->prepareBridge()) {
    if ($cms->isError()) {
      // error initializing the CMS bridge
      trigger_error($cms->getError(), E_USER_ERROR);
    }
    elseif ($cms->getStatus() == cmsBridge::STATUS_NO_CONFIGURATION) {
      // try to get the configuration
      if (!$cms->detectCMS()) {
        trigger_error($cms->getError(), E_USER_ERROR);
      }
      if (!$cms->prepareBridge()) {
        $error = sprintf('[%s] %s', __LINE__, 'Give up, failed to initialize the CMS bridge!');
        $logger->addCritical($error);
        trigger_error($error, E_USER_ERROR);
      }
    }
    else {
      // Ooops?
      $error = sprintf('[%s] %s', __LINE__, 'Failed to init the CMS configuration');
      $logger->addCritical($error);
      trigger_error($error, E_USER_ERROR);
    }
  }

  // the cmsBrige is prepared and spend the settings for the database connection
  $config = new \Doctrine\DBAL\Configuration();
  $connectionParams = array(
      'dbname' => $cms->getCMS_DB_NAME(),
      'user' => $cms->getCMS_DB_USERNAME(),
      'password' => $cms->getCMS_DB_PASSWORD(),
      'host' => $cms->getCMS_DB_HOST(),
      'port' => $cms->getCMS_DB_PORT(),
      'driver' => 'pdo_mysql',
  );
  $db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

  // ok - now we can finish the initialization of the cmsBridge
  if (!$cms->initBridge())
    trigger_error($cms->getError(), E_USER_ERROR);

  // connect with the CMS access helpers
  if (!defined('WB_PATH')) {
    // bootstrap.php is not called by the CMS we must initialize
    require_once CMS_PATH.'/config.php';
    // the CMS was inialized with an external access
    if (!defined('EXTERNAL_ACCESS'))
      define('EXTERNAL_ACCESS', true);
  }
  else {
    // regular execution of the CMS
    if (!defined('EXTERNAL_ACCESS'))
      define('EXTERNAL_ACCESS', false);
  }

  global $I18n;
  // we use the I18n service from the addon manufakturConfig
  if (!class_exists('LEPTON_Helper_I18n'))
    require_once CMS_PATH.'/modules/manufaktur_config/framework/LEPTON/Helper/I18n.php';
  // detect and set the actual language
  if (!$cms->setLanguage(CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/Data/Location/'))
    trigger_error($cms->getError());
  // initialize the I18n service
  if (!is_object($I18n))
    $I18n = new LEPTON_Helper_I18n();
  // add the needed language file if possible
  if (file_exists(CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/Data/Location/'.CMS_LANGUAGE.'.php'))
    $I18n->addFile(CMS_LANGUAGE.'.php', CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/Data/Location/');

  global $dwoo;
  // we use the Dwoo template engine (as external addon)
  require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/dwooAutoload.php';
  // initialize the template engine
  if (!is_object($dwoo)) {
    $cache_path = CMS_TEMP_PATH.'/cache';
    if (!file_exists($cache_path))
      mkdir($cache_path, 0755, true);
    $compiled_path = CMS_TEMP_PATH.'/compiled';
    if (!file_exists($compiled_path))
      mkdir($compiled_path, 0755, true);
    $dwoo = new Dwoo($compiled_path, $cache_path);
  }
  // load extensions for the Dwoo template engine
  $loader = $dwoo->getLoader();
  // Dwoo plugins for the cmsBridge, i.e. wysiwygEditor()
  $loader->addDirectory(CMS_ADDON_PATH.'/vendor/phpManufaktur/CMS/Bridge/View/Templates/Plugins/');



  if (EXTERNAL_ACCESS) {
    // redirect to the Settings tool
    $Settings = new controlSettings();
    $Settings->action();
    exit();
  }

} // !is_object($cms)
