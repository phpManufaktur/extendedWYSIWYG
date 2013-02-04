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
      `description` TEXT NOT NULL,
      `root_parent` INT(11) NOT NULL DEFAULT '0',
      `status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY (`name`),
      UNIQUE (`root_parent`)
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
      $SQL = "SELECT `page_id`,$pages.`root_parent`,`level`,`page_title`,`menu_title` FROM `$pages` ".
        "LEFT JOIN $department ON (CONVERT($pages.root_parent USING utf8)=CONVERT($department.root_parent USING utf8)) ".
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
  public function selectAllDepartments($active_only=false) {
    global $db;
    global $tools;

    try {
      if ($active_only) {
        $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `status`='ACTIVE' ORDER BY `name` ASC";
      }
      else {
        $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `status`!='DELETED' ORDER BY `name` ASC";
      }
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $departments = array();
    if (is_array($result)) {
      foreach ($result as $department) {
        $departments[] = array(
            'id' => $department['id'],
            'name' => $tools->unsanitizeText($department['name']),
            'description' => $tools->unsanitizeText($department['description']),
            'root_parent' => $department['root_parent'],
            'status' => $department['status'],
            'timestamp' => $department['timestamp']
            );
      }
    }
    return $departments;
  } // selectAllDepartments()

  /**
   * Select the desired department id and return the complete record
   *
   * @param integer $department_id
   * @return boolean|multitype:Ambigous <string, mixed>
   */
  public function select($department_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `id`=:id";
      $result = $db->fetchAssoc($SQL, array('id' => $department_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $department = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $department[$key] = $tools->unsanitizeText($value);
    }
    return $department;
  } // select()

  /**
   * Check if the editorial departments are configured as single root department.
   * If success return the ID of the root department
   *
   * @return boolean|number
   */
  public function getDepartmentRootId() {
    global $db;

    try {
      $SQL = "SELECT `id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `status`='ACTIVE' AND `root_parent`='0'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['id'])) ? $result['id'] : -1;
  } // getDepartmentId()

  /**
   * Insert a new department. The $department array must contain the data.
   *
   * @param array $department
   * @param integer $new_id contains the ID of the inserted record
   * @return boolean
   */
  public function insert($department, &$new_id=-1) {
    global $db;
    global $tools;
    global $I18n;

    try {
      // prepare the data
      $data = array(
          'name' => (isset($department['name'])) ? $tools->sanitizeText($department['name']) : '- no name -',
          'description' => (isset($department['description'])) ? $tools->sanitizeText($department['description']) : '',
          'root_parent' => (isset($department['root_parent'])) ? (int) $department['root_parent'] : 0,
          'status' => (isset($department['status'])) ? $department['status'] : 'ACTIVE'
          );
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department', $data);
      $new_id = $db->lastInsertId();
      $this->setInfo($I18n->translate('Inserted a new department with the ID {{ id }}', array('id' => $new_id)), __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insert()

  /**
   * Update the desired department record
   *
   * @param integer $department_id
   * @param array $data
   * @return boolean
   */
  public function update($department_id, $data) {
    global $db;
    global $tools;

    $department = array();
    foreach ($data as $key => $value)
      $department[$key] = $tools->sanitizeVariable($value);

    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department', $department, array('id' => $department_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // update()

  /**
   * Check if the given page id is a valid department root id
   *
   * @param integer $page_id
   * @return boolean
   */
  public function isDepartmentRootId($page_id) {
    global $db;

    try {
      $SQL = "SELECT `id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_department` WHERE `root_parent`='$page_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['id'])) ? true : false;
  } // isDepartmentRootId()

  /**
   * Delete the department with the given ID
   *
   * @param integer $department_id
   * @return boolean
   */
  public function delete($department_id) {
    global $db;

    try {
      $db->delete(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department', array('id' => $department_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // delete()

  /**
   * Select the department ID for the given page ID. If there exists no department
   * for this page ID the query returns -1
   *
   * @param integer $page_id
   * @return boolean|number
   */
  public function getDepartmentIdForPageId($page_id) {
    global $db;

    $department = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_department';
    $pages = CMS_TABLE_PREFIX.'pages';

    try {
      $SQL = "SELECT `id` FROM $department LEFT JOIN $pages ON ".
        "(CONVERT($department.root_parent USING utf8)=CONVERT($pages.root_parent USING utf8)) WHERE page_id='$page_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['id'])) ? (int) $result['id'] : -1;
  } // getDepartmentIdForPageId()


} // class editorTeam
