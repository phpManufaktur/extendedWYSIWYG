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

class wysiwygExtension extends boneClass {

  /**
   * Create the table mod_wysiwyg_extension
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg_extension';
    $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `extension_id` INT (11) NOT NULL AUTO_INCREMENT,
      `section_id` INT(11) NOT NULL DEFAULT '0',
      `page_id` INT(11) NOT NULL DEFAULT '0',
      `options` VARCHAR(255) NOT NULL DEFAULT '0',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`extension_id`),
      KEY (`section_id`, `page_id`)
    )
    COMMENT='extendedWYSIWYG Extension enables additional features for WYSIWYG'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg_extension', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Select the options for SECTION ID and PAGE ID and return a array with the
   * active options
   *
   * @param integer $page_id
   * @param integer $section_id
   * @return boolean|multitype:
   */
  public function selectOptions($page_id, $section_id) {
    global $db;

    try {
      $SQL = "SELECT `options` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_extension` WHERE `page_id`='$page_id' AND `section_id`='$section_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $options = array();
    if (is_array($result)) {
      $options = explode(',', $result['options']);
    }
    return $options;
  } // selectOptions

  /**
   * Check if a Options record exists for PAGE ID and SECTION ID
   *
   * @param integer $page_id
   * @param integer $section_id
   * @return boolean
   */
  public function existsOptions($page_id, $section_id) {
    global $db;

    try {
      $SQL = "SELECT `options` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_extension` WHERE `page_id`='$page_id' AND `section_id`='$section_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (is_array($result) && isset($result['options']));
  } // existsOptions()

  public function insertOptions($page_id, $section_id, $options=array(), &$extension_id=-1) {
    global $db;

    $data = array(
        'section_id' => (int) $section_id,
        'page_id' => (int) $page_id,
        'options' => implode(',', $options)
        );
    try {
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_extension', $data);
      $extension_id = $db->lastInsertId();
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insertOptions()

  public function updateOptions($page_id, $section_id, $options=array()) {
    global $db;

    $where = array(
        'section_id' => (int) $section_id,
        'page_id' => (int) $page_id
    );
    $data = array(
        'options' => implode(',', $options)
        );
    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg_extension', $data, $where);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // updateOptions()

} // class wysiwygExtension