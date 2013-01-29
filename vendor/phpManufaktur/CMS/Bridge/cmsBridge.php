<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\Control\detectCMS;
use phpManufaktur\CMS\Bridge\Data\LEPTON as LEPTON;
use phpManufaktur\CMS\Bridge\Data\WebsiteBaker as WebsiteBaker;
use phpManufaktur\CMS\Bridge\Data\browserLanguage;

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
    $media = new LEPTON\mediaDirectory();
    if (false === ($media_directory = $media->getDirectoryName()))
      return false;
    self::$config['CMS_MEDIA_DIRECTORY'] = $media_directory;
    return true;
  }

  public function getCMS_MEDIA_PATH() {
    return (isset(self::$config['CMS_MEDIA_DIRECTORY']) && isset(self::$config['CMS_PATH'])) ? self::$config['CMS_PATH'].self::$config['CMS_MEDIA_DIRECTORY'] : null;
  }

  public function getCMS_MEDIA_URL() {
    return (isset(self::$config['CMS_MEDIA_DIRECTORY']) && isset(self::$config['CMS_URL'])) ? self::$config['CMS_URL'].self::$config['CMS_MEDIA_DIRECTORY'] : null;
  }

  public function getCMS_ADDON_URL() {
    if (isset(self::$config['CMS_PATH']) && isset(self::$config['CMS_URL'])) {
      return self::$config['CMS_URL'] . substr(CMS_ADDON_PATH, strlen(self::$config['CMS_PATH']));
    }
    else
      return null;
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
    $lepton_version = new LEPTON\Version();
    if (!$lepton_version->check()) {
      if ($lepton_version->isError()) {
        return false;
      }
      // ok - try WebsiteBaker
      $wb_version = new WebsiteBaker\Version();
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
    if (!defined('CMS_PATH'))
      define('CMS_PATH', $this->getCMS_PATH());
    if (!defined('CMS_URL'))
      define('CMS_URL', $this->getCMS_URL());
    if (!defined('CMS_ADMIN_PATH'))
      define('CMS_ADMIN_PATH', $this->getCMS_ADMIN_PATH());
    if (!defined('CMS_ADMIN_URL'))
      define('CMS_ADMIN_URL', $this->getCMS_ADMIN_URL());
    if (!defined('CMS_TABLE_PREFIX'))
      define('CMS_TABLE_PREFIX', $this->getCMS_TABLE_PREFIX());
    if (!defined('CMS_TEMP_PATH'))
      define('CMS_TEMP_PATH', $this->getCMS_TEMP_PATH());
    if (!defined('CMS_TEMP_URL'))
      define('CMS_TEMP_URL', $this->getCMS_TEMP_URL());
    if (!defined('CMS_ADDON_URL'))
      define('CMS_ADDON_URL', $this->getCMS_ADDON_URL());

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
    if (!defined('CMS_LANGUAGE'))
      define('CMS_LANGUAGE', self::$config['CMS_LANGUAGE']);
    return true;
  } // setLanguage()

  /**
   * Check wether a user is authenticated for the CMS or not
   *
   * @return boolean
   */
  public function isUserAuthenticated() {
    return (isset($_SESSION['USER_ID']) && ($_SESSION['USER_ID'] != '') && is_numeric($_SESSION['USER_ID']));
  } // isUserAuthenticated()

  /**
   * Get the login name of the authenticated user.
   * If not authenticated the function return an empty string
   *
   * @return Ambigous <string, unknown>
   */
  public function getUserLoginName() {
    return (isset($_SESSION['USERNAME'])) ? $_SESSION['USERNAME'] : '';
  } // getUserLoginName()

  /**
   * Get the Display Name of the authenticated user.
   * If not authenticated the function return an empty string
   *
   * @return Ambigous <string, unknown>
   */
  public function getUserDisplayName() {
    return (isset($_SESSION['DISPLAY_NAME'])) ? $_SESSION['DISPLAY_NAME'] : '';
  } // getUserDisplayName()

  /**
   * Get the User ID of the authenticated user.
   * If not authenticated return -1
   *
   * @return Ambigous <string, unknown>|number
   */
  public function getUserID() {
    return (isset($_SESSION['USER_ID'])) ? $_SESSION['USER_ID'] : -1;
  }

  /**
   * Get the EMail Address of the authenticated user.
   * If not authenticated the function return an empty string
   *
   * @return Ambigous <string, unknown>
   */
  public function getUserEMailAddress() {
    global $wb;
    global $admin;

    // use the WB / LEPTON function
    if (is_object($wb))
      return $wb->get_email();
    elseif (is_object($admin))
      return $admin->get_email();
    else
      return '';
  } // getUserEMailAddress

  /**
   * Get the relative directory for the desired $page_id
   *
   * @param integer $page_id
   * @return boolean|string
   */
  public function getDirectoryForPageID($page_id) {
    global $I18n;

    $settings = new LEPTON\Settings();
    if (false === ($directory = $settings->select('pages_directory'))) {
      $this->setError($settings->getError(), __METHOD__, __LINE__);
      return false;
    }
    $pages = new LEPTON\Page();
    if (false === ($page = $pages->select($page_id))) {
      $this->setError($pages->getError(), __METHOD__, __LINE__);
      return false;
    }
    if (count($page) < 1) {
      $this->setError($I18n->translate('<p>There exists no entry for the page ID {{ page_id }}!</p>',
          array('page_id' => $page_id)));
      return false;
    }
    return $directory.$page['link'];
  } // getDirectoryForPageID()

} // class cmsBridge