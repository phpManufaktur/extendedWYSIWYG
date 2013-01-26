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

class editorDepartment extends boneClass {

  /**
   * Create the table mod_wysiwyg_editor_team
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department';
$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL DEFAULT '',
      `root_parent` INT(11) NOT NULL DEFAULT '0',
      `status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY (`name`, `root_parent`)
    )
    COMMENT='The departments of the editorial team'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_editor_department', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Select the available root_parent pages as entrypoint for the departments,
   * without already as departments used pages.
   * The CMS/Data/LEPTON/Pages/fixRootParentProblem() should be executed first!
   *
   * @param integer $max_level of pages
   * @return boolean|multitype:
   */
  public function selectPagesList($max_level) {
    global $db;

    $pages = CMS_TABLE_PREFIX.'pages';
    $department = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department';

    try {
      $SQL = "SELECT `page_id`,$pages.`root_parent`,`level`,`page_title` FROM `$pages` ".
        "LEFT JOIN $department ON ($pages.root_parent=$department.root_parent) ".
        "WHERE $department.root_parent IS NULL AND `level`<'$max_level' ORDER BY `root_parent`, `level`, `page_title` ASC";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // selectPagesList

  /**
   * Select all active and locked editorial departments
   *
   * @return boolean|multitype:
   */
  public function selectAllDepartments() {
    global $db;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `status`!='DELETED' ORDER BY `name` ASC";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // selectAllDepartments()

} // class editorTeam
