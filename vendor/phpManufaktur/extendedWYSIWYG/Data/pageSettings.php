<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\extendedWYSIWYG\Data;

use phpManufaktur\CMS\Bridge\Control\boneClass;


class pageSettings extends boneClass {

  /**
   * Return a array with the page settings for Title, Description and Keywords
   *
   * @param integer $page_id
   * @return array
   */
  public function getSettingsArray($page_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT `page_title`, `description`, `keywords` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=:page_id";
      $result = $db->fetchAssoc($SQL, array('page_id' => $page_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    $settings = array();
    // loop through the result and unsanitize the returned values
    foreach ($result as $key => $value)
      $settings[$key] = $tools->unsanitizeText($value);
    return $settings;
  } // getOptionArray()

  /**
   * Update the $fields in the page table.
   *
   * @param integer $page_id
   * @param array $fields structured array with fieldnames and values
   * @return boolean|string
   */
  public function updateSettings($page_id, $fields=array()) {
    global $db;
    global $tools;
    global $I18n;

    if (count($fields) < 1) {
      // no fields to update!
      $message = $I18n->translate('<p>The fields array is empty, nothing to do!</p>');
      $this->setMessage($message, __METHOD__, __LINE__);
      return true;
    }

    // get the schema manager
    $schema = $db->getSchemaManager();
    // get the page columns
    $columns = $schema->listTableColumns(CMS_TABLE_PREFIX.'pages');
    // placeholder for the real processing fields
    $process_fields = array();
    // walk through the table fields and grant that they are existing
    foreach ($columns as $column) {
      $name = $column->getName();
      if (key_exists($name, $fields))
        // add the field and sanitize the value
        $process_fields[$name] = $tools->sanitizeText($fields[$name]);
    }
    if (count($process_fields) < 1) {
      // no fields to update!
      $message = $I18n->translate('<p>The fields array is empty, nothing to do!</p>');
      $this->setMessage($message, __METHOD__, __LINE__);
      return true;
    }

    try {
      $db->update(CMS_TABLE_PREFIX.'pages', $process_fields, array('page_id' => $page_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    return true;
  }

} // class pageSettings
