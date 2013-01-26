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

class editorTeam extends boneClass {

  /**
   * Create the table mod_wysiwyg_editor_team
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';
$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL DEFAULT '',
      `position` ENUM('CHIEF_EDITOR','SUB_CHIEF_EDITOR','EDITOR','TRAINEE') NOT NULL DEFAULT 'EDITOR',
      `supervisor` VARCHAR(255) NOT NULL DEFAULT '',
      `department` INT(11) NOT NULL DEFAULT '-1',
      `rights` BIGINT UNSIGNED NOT NULL DEFAULT '0',
      `last_activity` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY (`name`, `department`)
    )
    COMMENT='The members of the editorial department'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_editor_team', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Select CMS users which are not member of the editors team
   *
   * @return boolean|multitype:
   */
  public function selectAsEditorAvailableUsers() {
    global $db;

    $users = CMS_TABLE_PREFIX.'users';
    $editors = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';

    try {
      $SQL = "SELECT `user_id`, `username`, `display_name`, `email`  FROM $users LEFT JOIN $editors ".
        "ON ($users.username=$editors.name) WHERE $editors.name IS NULL AND $users.active='1'";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // selectAsEditorAvailableUsers()

} // class editorTeam
