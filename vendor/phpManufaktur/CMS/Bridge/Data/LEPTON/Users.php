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
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."users`";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $users = array();
    foreach ($result as $user) {
      $add = array();
      foreach ($user as $key => $value)
        $add[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;
      $users[] = $add;
    }
    return $users;
  } // selectAll()

  /**
   * Fetch the user display name from the given username
   *
   * @param string $name
   * @return boolean|Ambigous <string, mixed>
   */
  public function getUserDisplayName($name) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT `display_name` FROM `".CMS_TABLE_PREFIX."users` WHERE `username`='$name'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['display_name'])) ? $tools->unsanitizeText($result['display_name']) : $name;
  } // getUserDisplayName()

  /**
   * Fetch the user email address from the given username
   *
   * @param string $name
   * @return boolean|Ambigous <string, mixed>
   */
  public function getUserEMail($name) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT `email` FROM `".CMS_TABLE_PREFIX."users` WHERE `username`='$name'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['email'])) ? $tools->unsanitizeText($result['email']) : 'nobody@anonymous.tld';
  } // getUserEMail()

  /**
   * Fetch the user name form the give email address
   *
   * @param string $email
   * @return boolean|Ambigous <string, mixed>
   */
  public function getUserName($email) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT `username` FROM `".CMS_TABLE_PREFIX."users` WHERE `email`='$email'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['username'])) ? $tools->unsanitizeText($result['username']) : '- nobody -';
  } // getUserEMail()

  /**
   * Check if the given user has administrator privileges
   *
   * @param string $name
   * @return boolean
   */
  public function isAdministrator($name) {
    global $db;

    try {
      $SQL = "SELECT `group_id` FROM `".CMS_TABLE_PREFIX."users` WHERE `username`='$name' AND `group_id`='1'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['group_id'])) ? true : false;
  } // isAdministrator()

} // class Users