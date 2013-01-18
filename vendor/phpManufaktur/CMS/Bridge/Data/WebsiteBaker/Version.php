<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\WebsiteBaker\Classes;

use phpManufaktur\CMS\Classes\boneClass;

global $db;
global $logger;

class Version extends boneClass {

  protected static $version = null;

  /**
   * Return the Version of the desired Content Management System
   *
   * @return string
   */
  public function get() {
    return self::$version;
  } // get()

  /**
   * Get the version number of the Master CMS from the database settings
   *
   * @return boolean
   */
  public function check() {
    global $db;
    global $logger;

    $SQL = "SELECT `value` FROM `".CMS_TABLE_PREFIX."settings` WHERE `name`='wb_version'";
    try {
      $query = $db->query($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    if ($query->rowCount() != 1) {
      // no entry for the WebsiteBaker version
      return false;
    }
    // fetch the value
    $setting = $query->fetch();
    self::$version = $setting['value'];
    $this->setInfo("Got the WebsiteBaker version: ".self::$version, __METHOD__, __LINE__);
    return true;
  } // check()

} // class getVersion