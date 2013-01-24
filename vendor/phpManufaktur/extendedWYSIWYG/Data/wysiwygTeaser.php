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
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;

class wysiwygTeaser extends boneClass {

  /**
   * Create the table mod_wysiwyg_teaser
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_teaser';
$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `teaser_id` INT(11) NOT NULL AUTO_INCREMENT,
      `page_id` INT(11) NOT NULL DEFAULT '0',
      `teaser_text` TEXT NOT NULL DEFAULT '',
      `hash` VARCHAR(32) NOT NULL DEFAULT '',
      `author` VARCHAR(255) NOT NULL DEFAULT '',
      `date_publish` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `status` ENUM('ACTIVE','UNPUBLISHED','BACKUP') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`teaser_id`),
      KEY (`page_id`, `status`)
    )
    COMMENT='extendedWYSIWYG Teaser for the Blog function'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_teaser', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Select the Teaser with the ID $teaser_id and return an array
   *
   * @param integer $teaser_id
   * @return boolean|multitype:
   */
  public function select($teaser_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_teaser` WHERE `teaser_id`=:teaser_id";
      $result = $db->fetchAssoc($SQL, array('teaser_id' => $teaser_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $teaser = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $teaser[$key] = $tools->unsanitizeText($value);
    }
    return $teaser;
  } // select()

  /**
   * Select the last Teaser record for the given PAGE ID and return a array
   *
   * @param integer $page_id
   * @return boolean|multitype:
   */
  public function selectLast($page_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_teaser` WHERE `page_id`=:page_id ORDER BY `teaser_id` DESC LIMIT 1";
      $result = $db->fetchAssoc($SQL, array('page_id' => $page_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $teaser = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $teaser[$key] = $tools->unsanitizeText($value);
    }
    return $teaser;
  } // selectLast()

  /**
   * Get the last Teaser entries for the selection in the dialog.
   * Limit will be set by the configuration value, return a associative array.
   *
   * @param integer $page_id
   * @return boolean|multitype:
   */
  public function selectTeaserListForDialog($page_id) {
    global $db;

    $configuration = new wysiwygConfiguration();
    if (false === ($limit = (int) $configuration->getValue('cfgArchiveIdSelectLimit'))) {
      $this->setError($configuration->getError(), __METHOD__, __LINE__);
      return false;
    }

    try {
      $SQL = "SELECT `timestamp`, `status`, `teaser_id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_teaser` ".
          "WHERE `page_id`='$page_id' ORDER BY `teaser_id` DESC LIMIT $limit";
      $teaser = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $teaser;
  } // selectTeaserListForDialog()

  public function insert($page_id, $teaser, $author, $date, $status, &$teaser_id=-1) {
    global $db;
    global $tools;

    try {
      // first we set all previous teaser's with the same ACTUAL status for this PAGE_ID to BACKUP status
      $SQL = "UPDATE `".CMS_TABLE_PREFIX."mod_wysiwyg_teaser` SET `status`='BACKUP' WHERE `page_id`='$page_id' AND `status`='$status'";
      $db->query($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    $data = array(
        'page_id' => $page_id,
        'teaser_text' => $tools->sanitizeText($teaser),
        'hash' => md5($teaser),
        'author' => $tools->sanitizeText($author),
        'date_publish' => $date,
        'status' => $status
        );

    try {
      // insert the new teaser
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_teaser', $data);
      $teaser_id = $db->lastInsertId();
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insert()

} // class wysiwygTeaser