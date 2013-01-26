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

class Pages extends boneClass {

  /**
   * Select the desired page with the $page_id and return the complete record
   *
   * @param integer $page_id
   * @return boolean|multitype:Ambigous <string, mixed>
   */
  public function select($page_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $page = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $page[$key] = $tools->unsanitizeText($value);
    }
    return $page;
  } // select()

  /**
   * Sometime WB/LEPTON does not set the root_parent field if the root parent
   * page is created. The field root_parent will contain '0' but need the
   * page_id of this page. This update fix this problem.
   *
   * @return boolean
   */
  public function fixRootParentProblem() {
    global $db;

    try {
      $SQL = "UPDATE `".CMS_TABLE_PREFIX."pages` SET `root_parent`=`page_id` WHERE `root_parent`='0'";
      $result = $db->query($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // fixRootLevelProblem()

} // class mediaDirectory
