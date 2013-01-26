<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data\LEPTON;

use phpManufaktur\CMS\Bridge\Control\boneClass;

global $db;

class Users extends boneClass {

  /**
   * Check if the user with the $username and $password can be authenticated.
   * If $as_administrator is set to true, the user must also be administrator.
   *
   * @param string $username
   * @param string $password
   * @param boolean $as_administrator
   * @return boolean
   */
  public function checkLogin($username, $password, $as_administrator=false) {
    global $db;

    try {
      $SQL = "SELECT `password`, `groups_id` FROM `".CMS_TABLE_PREFIX."users` WHERE `username`='$username' AND `active`='1' AND `statusflags`>'1'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    if (isset($result['password']) && (md5($password) == $result['password'])) {
      if ($as_administrator) {
        $groups = explode(',', $result['groups_id']);
        if (in_array(1, $groups))
          return true;
      }
      else
        return true;
    }
    return false;
  } // checkLogin

  /**
   * Fetch all users from the table and return an associative array with all fields
   *
   * @return boolean|multitype:
   */
  public function selectAll() {
    global $db;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."users`";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // selectAll()

} // class Users