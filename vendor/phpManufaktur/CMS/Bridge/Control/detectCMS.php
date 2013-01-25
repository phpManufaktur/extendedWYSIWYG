<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Control;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\Data\parseTokens;

global $logger;

class detectCMS extends boneClass {

  protected static $defines = array(
      'DB_TYPE',
      'DB_HOST',
      'DB_PORT',
      'DB_USERNAME',
      'DB_PASSWORD',
      'DB_NAME',
      'TABLE_PREFIX',
      'WB_PATH',
      'WB_URL'
      );
  protected static $configuration = array();

  /**
   * Search for the configuration file of the CMS (which is the ROOT)
   *
   * @param string $config_file_path
   * @return boolean
   */
  protected function searchConfigurationFile(&$root_path='') {
    $root = __DIR__;
    // at maximum step 8 levels back!
    for ($i=0; $i < 8; $i++) {
      $root = substr($root, 0, strrpos($root, '/'));
      // at this point we really want no error messages!
      if (@file_exists($root.'/config.php')) {
        $root_path = $root;
        $this->setInfo("Detect config.php at path: $root", __METHOD__, __LINE__);
        return true;
      }
    }
    $this->setError("No config file detected!", __METHOD__, __LINE__);
    return false;
  } // searchConfigurationFile()

  /**
   * Read the configuration file from the ROOT
   *
   * @param string $root_path
   * @return boolean
   */
  protected function readConfigurationFile($root_path='') {
    $tokens = new parseTokens();
    $defines = $tokens->parseFileForDefines($root_path.'/config.php');
    if (count($defines) < 1) {
      $this->setInfo("Got no definitions from $root_path/config.php", __METHOD__, __LINE__);
      return false;
    }
    $this->setInfo('Got '.count($defines)." definitions from the config file.", __METHOD__, __LINE__);

    // no we step through the definitions
    $config_file = array();
    $loop_defines = self::$defines;

    if (isset($defines['ADMIN_DIRECTORY'])) {
      // it's WB 2.8.2 or above
      $loop_defines[] = 'ADMIN_DIRECTORY';
    }
    else {
      // WB 2.8.1 or LEPTON
      $loop_defines[] = 'ADMIN_URL';
      $loop_defines[] = 'ADMIN_PATH';
    }

    foreach ($loop_defines as $define) {
      if (!isset($defines[$define])) {
        $this->setError("Missing the definition for $define, must abort", __METHOD__, __LINE__);
        return false;
      }
      switch ($define):
      case 'WB_PATH':
        $config_file['CMS_PATH'] = $root_path;
        break;
      case 'WB_URL':
        $config_file['CMS_URL'] = $defines[$define];
        break;
      case 'ADMIN_PATH':
        $admin = substr($defines['ADMIN_URL'], strlen($defines['WB_URL']));
        $config_file['CMS_ADMIN_PATH'] = $root_path.$admin;
        break;
      case 'ADMIN_DIRECTORY':
        $config_file['ADMIN_PATH'] = $root_path.DIRECTORY_SEPARATOR.$defines[$define];
        $config_file['ADMIN_URL'] = $defines['WB_URL'].DIRECTORY_SEPARATOR.$defines[$define];
        break;
      default:
        $config_file["CMS_$define"] = $defines[$define];
      endswitch;
    }

    if (isset($defines['LEPTON_GUID']))
      $config_file['CMS_TYPE'] = 'LEPTON';
    else
      $config_file['CMS_TYPE'] = 'WebsiteBaker';

    $config_file['CMS_ADDON_PATH'] = CMS_ADDON_PATH;
    $config_file['CMS_ADDON_CONFIG_PATH'] = CMS_ADDON_CONFIG_PATH;

    $this->setInfo('Got all definitions from the config file.', __METHOD__, __LINE__);
    self::$configuration = $config_file;
    return true;
  } // readConfigurationFile()

  /**
   * Write the configuration settings to /config/cmsConfig.json
   *
   * @return boolean
   */
  protected function writeConfigurationFile() {
    $config_file = self::$configuration['CMS_ADDON_CONFIG_PATH'].'/cmsConfig.json';
    if (!file_put_contents($config_file, json_encode(self::$configuration))) {
      $this->setError("Error writing the configuration file $config_file", __METHOD__, __LINE__);
      return false;
    }
    $this->setInfo("Created the CMS config file $config_file", __METHOD__, __LINE__);
    return true;
  } // writeConfigurationFile

  /**
   * Search the CMS configuration file, read it and create a new
   * configuration file for the Addon
   *
   * @return boolean
   */
  public function checkConfiguration() {
    $config_file = null;
    if (!$this->searchConfigurationFile($config_file))
      return false;
    if (!$this->readConfigurationFile($config_file))
      return false;
    if (!$this->writeConfigurationFile())
      return false;
    return true;
  } // check()

} // class detectCMS