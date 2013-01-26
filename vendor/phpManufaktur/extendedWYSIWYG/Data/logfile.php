<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Data;

use phpManufaktur\CMS\Bridge\Control\boneClass;

class logfile extends boneClass {

  public static $level_array = array(
      'DEBUG' => 100,
      'INFO' => 200,
      'NOTICE' => 250,
      'WARNING' => 300,
      'ERROR' => 400,
      'CRITICAL' => 500,
      'ALERT' => 550,
      'EMERGENCY' => 600
      );

  /**
   * Read the the logfile and return the entries separated by <br />.
   *
   * @param boolean $reverse the entries and set last to the top
   * @return string logfile
   */
  public function getLog($reverse=false) {
    global $I18n;

    // read the logfile
    $logfile = '';
    if (!file_exists(CMS_ADDON_PATH.'/logfile/extendedWYSIWYG.log')) {
      $this->setMessage($I18n->translate('<p>The LOGFILE does not exists!</p>'), __METHOD__, __LINE__);
    }
    else {
      if (false === ($log_array = file(CMS_ADDON_PATH.'/logfile/extendedWYSIWYG.log',
          FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))) {
        $this->setMessage($I18n->translate('<p>Can\'read the LOGFILE!</p>'), __METHOD__, __LINE__);
      }
      else {
        // reverse array and explode the lines to text
        if ($reverse)
          $log_array = array_reverse($log_array);
        $logfile = implode("<br />", $log_array);
      }
    }
    return $logfile;
  } // getLog()

  /**
   * Get the actual logger level as integer
   *
   * @return integer
   */
  public function getLoggerLevelInt() {
    return CMS_LOGGER_LEVEL;
  } // getLoggerLevelInt()

  /**
   * Get the actual logger level as name
   *
   * @return string
   */
  public function getLoggerLevelName() {
    global $logger;
    return $logger->getLevelName(CMS_LOGGER_LEVEL);
  } // getLoggerLevelName()

  public function changeLoggerLevel($level) {
    global $I18n;

    $config_file = CMS_ADDON_CONFIG_PATH.'/addonConfig.json';

    if (file_exists($config_file)) {
      if (false === ($addonConfig = json_decode(file_get_contents($config_file), true))) {
        $this->setError($I18n->translate('Can`t read the file {{ file }}.',
            array('file' => $config_file)), $method, $line);
        return false;
      }
      $addonConfig['logger']['level'] = (int) $level;
      if (!file_put_contents($config_file, json_encode($addonConfig))) {
        $this->setError($I18n->translate('Cant\'t write the file {{ file }}', array('file' => $config_file)));
        return false;
      }
    }
    else {
      // create the config file
      $addonConfig = array(
          'logger' => array(
              'level' => (int) $level
              )
          );
      if (!file_put_contents($config_file, json_encode($addonConfig))) {
        $this->setError($I18n->translate('Cant\'t write the file {{ file }}', array('file' => $config_file)));
        return false;
      }
    }
    return true;
  } // changeLoggerLevel()

} // class logfile