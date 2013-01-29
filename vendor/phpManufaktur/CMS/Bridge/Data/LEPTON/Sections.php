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

class Sections extends boneClass {

  /**
   * Select all sections with the desired PAGE_ID
   *
   * @param integer $page_id
   * @return boolean|multitype:
   */
  public function selectAllSections($page_id) {
    global $db;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."sections` WHERE `page_id`='$page_id'";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (is_array($result)) ? $result : array();
  } // selectAllSections()

  /**
   * Delete the desired section ID
   *
   * @param integer $section_id
   * @return boolean
   */
  public function delete($section_id) {
    global $db;

    try {
      $db->delete(CMS_TABLE_PREFIX.'sections', array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // delete()

} // class Sections