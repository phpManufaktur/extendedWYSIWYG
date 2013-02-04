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

class wysiwygMessages extends boneClass {

  /**
   * Create the table mod_wysiwyg_messages
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_messages';
$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `section_id` INT(11) NOT NULL DEFAULT '0',
      `page_id` INT(11) NOT NULL DEFAULT '0',
      `archive_id` INT(11) NOT NULL DEFAULT '0',
      `department_id` INT(11) NOT NULL DEFAULT '0',
      `content` LONGTEXT NOT NULL,
      `from_editor` VARCHAR(255) NOT NULL DEFAULT '',
      `to_editor` VARCHAR(255) NOT NULL DEFAULT '',
      `status` ENUM('PENDING','SEEN') NOT NULL DEFAULT 'PENDING',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY (`section_id`, `page_id`, `archive_id`)
    )
    COMMENT='extendedWYSIWYG Messages'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_messages', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Insert a new message
   *
   * @param array $data
   * @param integer $message_id returned reference
   * @return boolean
   */
  public function insert($data, &$message_id=-1) {
    global $db;
    global $I18n;
    global $tools;

    $insert = array();
    foreach ($data as $key => $value) {
      $insert[$key] = (is_string($value)) ? $tools->sanitizeVariable($value) : $value;
    }

    try {
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_messages', $data);
      $archive_id = $db->lastInsertId();
      $this->setInfo($I18n->translate('Insert a message from {{ from_editor }} to {{ to_editor }}',
          array('from_editor' => isset($data['from_editor']) ? $data['from_editor'] : '???',
              'to_editor' => isset($data['to_editor']) ? $data['to_editor'] : '???')),
          __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insert()

  /**
   * Select pending message by archive id for the given editor
   *
   * @param integer $archive_id
   * @param string $to_editor
   * @return boolean|multitype:Ambigous <string, mixed, unknown>
   */
  public function selectPendingByArchiveIdAndToEditor($archive_id, $to_editor) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_messages` WHERE ".
        "`archive_id`='$archive_id' AND `to_editor`='$to_editor' AND `status`='PENDING'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $message = array();
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $message[$key] = (is_string($value)) ? $tools->unsanitizeText($value) : $value;
    }
    return $message;
  } // selectByArchiveIdAndToEditor

  public function selectPendingsForEditorBySection($editor_name, $section_id) {
    global $db;
    global $tools;

    $msg = CMS_TABLE_PREFIX."mod_wysiwyg_messages";
    $usr = CMS_TABLE_PREFIX."users";

    try {
      $SQL = "SELECT * FROM `$msg` LEFT JOIN `$usr` ON (CONVERT($msg.from_editor USING utf8) = CONVERT($usr.username USING utf8)) ".
        "WHERE `to_editor`='$editor_name' AND `status`='PENDING' AND `section_id`='$section_id' ORDER BY `id` DESC";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $messages = array();
    if (is_array($result)) {
      foreach ($result as $message) {
        $add = array();
        foreach ($message as $key => $value)
          $add[$key] = (is_string($value)) ? $tools->unsanitizeText($value) : $value;
        $messages[] = $add;
      }
    }
    return $messages;
  } // selectPendingsForEditor()

  public function updatePendingsToSeenForEditorBySection($editor_name, $section_id) {
    global $db;

    try {
      $db->update(CMS_TABLE_PREFIX."mod_wysiwyg_messages", array('status' => 'SEEN'),
          array('to_editor' => $editor_name, 'section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // updatePendingsToSeenForEditorBySection()

} // class wysiwygMessages
