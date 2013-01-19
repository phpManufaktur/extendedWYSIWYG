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

class wysiwygArchive extends boneClass {

  /**
   * Create the table mod_wysiwyg_archive
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_archive';
    $SQL = <<<EOD
      CREATE TABLE IF NOT EXISTS `$table` (
      `archive_id` INT(11) NOT NULL AUTO_INCREMENT,
      `section_id` INT(11) NOT NULL DEFAULT '0',
      `page_id` INT(11) NOT NULL DEFAULT '0',
      `content` LONGTEXT NOT NULL,
      `hash` VARCHAR(32) NOT NULL DEFAULT '',
      `remark` VARCHAR(255) NOT NULL DEFAULT '',
      `author` VARCHAR(255) NOT NULL DEFAULT '',
      `status` ENUM('ACTIVE','UNPUBLISHED','BACKUP') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`archive_id`),
      KEY (`section_id`, `page_id`, `status`)
      )
      COMMENT='extendedWYSIWYG Archive'
      ENGINE=InnoDB
      AUTO_INCREMENT=1
      DEFAULT CHARSET=utf8
      COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_archive', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Select the Archive with the ID $archive_id and return an array
   *
   * @param integer $archive_id
   * @return boolean|multitype:
   */
  public function select($archive_id) {
    global $db;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` WHERE `archive_id`=:archive_id";
      $archive = $db->fetchAssoc($SQL, array('archive_id' => $archive_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $archive;
  } // select()



} // class wysiwygArchive
