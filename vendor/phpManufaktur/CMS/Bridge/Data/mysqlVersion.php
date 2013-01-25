<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data;

use phpManufaktur\CMS\Bridge\Control\boneClass;

class mysqlVersion extends boneClass {

  /**
   * Get the MySQL version number from the server
   *
   * @return boolean|number|Ambigous <>
   */
  public function get() {
    global $db;
    global $I18n;

    try {
      $SQL = "select version() as version";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    if (!isset($result['version'])) {
      $this->setMessage($I18n->translate('<p>Can\'t get the MySQL version number!</p>'), __METHOD__, __LINE__);
      return -1;
    }
    return $result['version'];
  } // get()

} // class mysqlVersion