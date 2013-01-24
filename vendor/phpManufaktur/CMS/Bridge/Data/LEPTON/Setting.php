<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data\LEPTON;

use phpManufaktur\CMS\Bridge\Control\boneClass;

global $db;

class Setting extends boneClass {

  /**
   * Get the value for given setting from the LEPTON configuration
   *
   * @param string $setting
   * @return boolean|Ambigous <>
   */
  public function select($setting) {
    global $db;

    try {
      $SQL = "SELECT `value` FROM `".CMS_TABLE_PREFIX."settings` WHERE `name`= ?";
      $this->setInfo("SQL: $SQL", __METHOD__, __LINE__);
      $result = $db->fetchAssoc($SQL, array($setting));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['value'])) ? $result['value'] : null;
  } // get()

}