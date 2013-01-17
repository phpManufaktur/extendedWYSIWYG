<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS;

use phpManufaktur\CMS\Classes\boneClass;
use phpManufaktur\CMS\Classes\detectCMS;
use phpManufaktur\CMS\Bridge\LEPTON as LEPTON;
use phpManufaktur\CMS\Bridge\WebsiteBaker as WebsiteBaker;
use phpManufaktur\CMS\Classes\browserLanguage;

global $logger;
global $db;

class cmsBridge extends boneClass {

  public $logger = null;
  public $db = null;

  const STATUS_INITIALIZED = 100;
  const STATUS_INACTIVE = 200;
  const STATUS_ERROR = 300;
  const STATUS_NO_CONFIGURATION = 400;
  const STATUS_PREPARED = 500;

  private static $status = self::STATUS_INACTIVE;
  private static $config = null;

  /**
   * Get the status of the cmsBridge
   */
  public static function getStatus() {
    return self::$status;
  } // getStatus()

  /**
   * Set the status of the cmsBridge
   */
  protected static function setStatus($status) {
    self::$status = $status;
  } // setStatus()

  public function getCMS_PATH() {
    return (isset(self::$config['CMS_PATH'])) ? self::$config['CMS_PATH'] : null;
  }

  public function getCMS_URL() {
    return (isset(self::$config['CMS_URL'])) ? self::$config['CMS_URL'] : null;
  }

  public function getCMS_ADMIN_PATH() {
    return (isset(self::$config['CMS_ADMIN_PATH'])) ? self::$config['CMS_ADMIN_PATH'] : null;
  }

  public function getCMS_ADMIN_URL() {
    return (isset(self::$config['CMS_ADMIN_URL'])) ? self::$config['CMS_ADMIN_URL'] : null;
  }

  public function getCMS_DB_TYPE() {
    return (isset(self::$config['CMS_DB_TYPE'])) ? self::$config['CMS_DB_TYPE'] : null;
  }

  public function getCMS_DB_HOST() {
    return (isset(self::$config['CMS_DB_HOST'])) ? self::$config['CMS_DB_HOST'] : null;
  }

  public function getCMS_DB_PORT() {
    return (isset(self::$config['CMS_DB_PORT'])) ? self::$config['CMS_DB_PORT'] : null;
  }

  public function getCMS_DB_USERNAME() {
    return (isset(self::$config['CMS_DB_USERNAME'])) ? self::$config['CMS_DB_USERNAME'] : null;
  }

  public function getCMS_DB_PASSWORD() {
    return (isset(self::$config['CMS_DB_PASSWORD'])) ? self::$config['CMS_DB_PASSWORD'] : null;
  }

  public function getCMS_DB_NAME() {
    return (isset(self::$config['CMS_DB_NAME'])) ? self::$config['CMS_DB_NAME'] : null;
  }

  public function getCMS_TABLE_PREFIX() {
    return (isset(self::$config['CMS_TABLE_PREFIX'])) ? self::$config['CMS_TABLE_PREFIX'] : null;
  }

  public function getCMS_TYPE() {
    return (isset(self::$config['CMS_TYPE'])) ? self::$config['CMS_TYPE'] : null;
  }

  public function getCMS_VERSION() {
    return (isset(self::$config['CMS_VERSION'])) ? self::$config['CMS_VERSION'] : null;
  }

  public function getCMS_TEMP_PATH() {
    return (isset(self::$config['CMS_PATH'])) ? self::$config['CMS_PATH'].'/temp' : null;
  }

  public function getCMS_TEMP_URL() {
    return (isset(self::$config['CMS_URL'])) ? self::$config['CMS_URL'].'/temp' : null;
  }

  /**
   * Get the MEDIA DIRECTORY from LEPTON/WB database settings and set the
   * CMS_MEDIA_DIRECTORY in the $config array
   *
   * @return boolean
   */
  public function setCMS_MEDIA_DIRECTORY() {
    global $db;
    if (self::$status != self::STATUS_INITIALIZED) {
      $this->setError('The cmsBridge must be initialized to use this function', __METHOD__, __LINE__);
      return false;
    }
    $SQL = "SELECT `value` FROM `".CMS_TABLE_PREFIX."settings` WHERE `name`='media_directory'";
    $this->setInfo("SQL: $SQL", __METHOD__, __LINE__);
    try {
      $query = $db->query($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    if ($query->rowCount() != 1) {
      // no entry for the media directory
      return false;
    }
    // fetch the value
    $setting = $query->fetch();
    self::$config['CMS_MEDIA_DIRECTORY'] = $setting['value'];
    return true;
  }

  public function getCMS_MEDIA_PATH() {
    return (isset(self::$config['CMS_MEDIA_DIRECTORY']) && isset(self::$config['CMS_PATH'])) ? self::$config['CMS_PATH'].self::$config['CMS_MEDIA_DIRECTORY'] : null;
  }

  public function getCMS_MEDIA_URL() {
    return (isset(self::$config['CMS_MEDIA_DIRECTORY']) && isset(self::$config['CMS_URL'])) ? self::$config['CMS_URL'].self::$config['CMS_MEDIA_DIRECTORY'] : null;
  }

  public function detectCMS() {
    $detectCMS = new detectCMS();
     if (!$detectCMS->checkConfiguration()) {
       $this->setError($detectCMS->getError(), __METHOD__, __LINE__);
       return false;
     }
     return true;
  } // detectCMS()

  /**
   * Get the version of the used CMS
   *
   * @return boolean
   */
  public function getCMSversion() {
    $lepton_version = new LEPTON\Classes\Version();
    if (!$lepton_version->check()) {
      if ($lepton_version->isError()) {
        return false;
      }
      // ok - try WebsiteBaker
      $wb_version = new WebsiteBaker\Classes\Version();
      if (!$wb_version->check()) {
        return false;
      }
      self::$config['CMS_TYPE'] = 'WebsiteBaker';
      self::$config['CMS_VERSION'] = $wb_version->get();
    }
    else {
      self::$config['CMS_TYPE'] = 'LEPTON';
      self::$config['CMS_VERSION'] = $lepton_version->get();
    }
    // set defines for easy access
    if (!defined('CMS_VERSION'))
      define('CMS_VERSION', self::$config['CMS_VERSION']);
    if (!defined('CMS_TYPE'))
      define('CMS_TYPE', self::$config['CMS_TYPE']);
    return true;
  }

  /**
   * Initialize the prepared cmsBridge
   *
   * @return boolean
   */
  public function initBridge() {
    if (self::$status != self::STATUS_PREPARED) {
      $this->setError('The cmsBridge must be prepared before inializing!', __METHOD__, __LINE__);
      return false;
    }
    // set the CMS Version and check the TYPE
    if (!$this->getCMSversion()) return false;

    // set status to initialized
    self::setStatus(self::STATUS_INITIALIZED);
    $this->setInfo('Class cmsBridge is initialized.', __METHOD__, __LINE__);

    // set the MEDIA Directory
    if (!$this->setCMS_MEDIA_DIRECTORY()) return false;
    if (!defined('CMS_MEDIA_PATH'))
      define('CMS_MEDIA_PATH', $this->getCMS_MEDIA_PATH());
    if (!defined('CMS_MEDIA_URL'))
      define('CMS_MEDIA_URL', $this->getCMS_MEDIA_URL());

    return true;
  } // initBridge()

  /**
   * Prepare the cmsBridge
   *
   * @return boolean
   */
  public function prepareBridge() {
    if (!defined('CMS_ADDON_CONFIG_PATH')) {
      $this->setError('CMS_ADDON_CONFIG_PATH is not defined', __METHOD__, __LINE__);
      return false;
    }
    // check if the configuration file exists
    if (!file_exists(CMS_ADDON_CONFIG_PATH.'/cmsConfig.json')) {
      $this->setInfo('Missing configuration file cmsConfig.json', __METHOD__, __LINE__);
      self::setStatus(self::STATUS_NO_CONFIGURATION);
      return false;
    }
    // load the configuration
    $this->setInfo('Load cmsConfig.json', __METHOD__, __LINE__);
    if (false === (self::$config = json_decode(file_get_contents(CMS_ADDON_CONFIG_PATH.'/cmsConfig.json'), true))) {
      $this->setError('Error reading the configuration file cmsConfig.json', __METHOD__, __LINE__);
      return false;
    }

    // set some CMS defines for easy access
    define('CMS_PATH', $this->getCMS_PATH());
    define('CMS_URL', $this->getCMS_TYPE());
    define('CMS_ADMIN_PATH', $this->getCMS_ADMIN_PATH());
    define('CMS_ADMIN_URL', $this->getCMS_ADMIN_URL());
    define('CMS_TABLE_PREFIX', $this->getCMS_TABLE_PREFIX());
    define('CMS_TEMP_PATH', $this->getCMS_TEMP_PATH());
    define('CMS_TEMP_URL', $this->getCMS_TEMP_URL());

    // set status to prepared
    self::setStatus(self::STATUS_PREPARED);
    $this->setInfo('Class cmsBridge is prepared for database connection.', __METHOD__, __LINE__);

    return true;
  } // prepareBridge()

  /**
   * Get the active language from the browser settings and set CMS_LANGUAGE
   *
   * @return boolean
   */
  public function setLanguage($language_path) {
    $browserLanguage = new browserLanguage();
    $available_languages = array();
    if (!$browserLanguage->getAvailableLanguages($language_path, $available_languages)) {
      $this->setError("Can't get the active browser language!", __METHOD__, __LINE__);
      return false;
    }
    self::$config['CMS_LANGUAGE'] = strtoupper($browserLanguage->get($available_languages, 'en'));
    if (!defined('CMS_LANGUGAGE'))
      define('CMS_LANGUAGE', self::$config['CMS_LANGUAGE']);
    return true;
  } // setLanguage()

} // class cmsBridge