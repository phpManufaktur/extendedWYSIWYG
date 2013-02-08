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

  const PERMISSION_POSITION_CHIEF_EDITOR = 512;
  const PERMISSION_POSITION_SUB_CHIEF_EDITOR = 1024;
  const PERMISSION_POSITION_EDITOR = 2048;
  const PERMISSION_POSITION_TRAINEE = 4096;

  const PERMISSION_SECTION_CREATE = 1;
  const PERMISSION_SECTION_VIEW = 8192; // last added
  const PERMISSION_SECTION_EDIT = 2;
  const PERMISSION_SECTION_LOCK = 4;
  const PERMISSION_SECTION_DELETE = 8;
  const PERMISSION_SECTION_RELEASE = 16;

  const PERMISSION_RELEASE_BY_OWN = 32;
  const PERMISSION_RELEASE_CHIEF_EDITOR_ONLY = 64;
  const PERMISSION_RELEASE_ONE_SUPERVISOR = 128;
  const PERMISSION_RELEASE_TWO_SUPERVISOR = 256;

  public static $position_permissions = array(
      self::PERMISSION_POSITION_CHIEF_EDITOR => 'POSITION_CHIEF_EDITOR',
      self::PERMISSION_POSITION_SUB_CHIEF_EDITOR => 'POSITION_SUB_CHIEF_EDITOR',
      self::PERMISSION_POSITION_EDITOR => 'POSITION_EDITOR',
      self::PERMISSION_POSITION_TRAINEE => 'POSITION_TRAINEE'
      );

  public static $section_permissions = array(
      self::PERMISSION_SECTION_CREATE => 'SECTION_CREATE',
      self::PERMISSION_SECTION_VIEW => 'SECTION_VIEW',
      self::PERMISSION_SECTION_EDIT => 'SECTION_EDIT',
      self::PERMISSION_SECTION_LOCK => 'SECTION_LOCK',
      self::PERMISSION_SECTION_DELETE => 'SECTION_DELETE',
      self::PERMISSION_SECTION_RELEASE => 'SECTION_RELEASE'
      );

  public static $release_permissions = array(
      self::PERMISSION_RELEASE_BY_OWN => 'RELEASE_BY_OWN',
      self::PERMISSION_RELEASE_CHIEF_EDITOR_ONLY => 'RELEASE_CHIEF_EDITOR_ONLY',
      self::PERMISSION_RELEASE_ONE_SUPERVISOR => 'RELEASE_ONE_SUPERVISOR',
      self::PERMISSION_RELEASE_TWO_SUPERVISOR => 'RELEASE_TWO_SUPERVISOR'
      );

  // predefined rights for default usage
  const DEFAULT_PERMISSION_CHIEF_EDITOR = 575;
  const DEFAULT_PERMISSION_SUB_CHIEF_EDITOR = 1087;
  const DEFAULT_PERMISSION_EDITOR = 2199;
  const DEFAULT_PERMISSION_TRAINEE = 4354;

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
      `supervisors` TEXT NOT NULL DEFAULT '',
      `departments` TEXT NOT NULL DEFAULT '',
      `permissions` BIGINT UNSIGNED NOT NULL DEFAULT '0',
      `last_activity` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`name`)
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
    global $tools;

    $users = CMS_TABLE_PREFIX.'users';
    $editors = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';

    try {
      $SQL = "SELECT `user_id`, `username`, `display_name`, `email`  FROM $users LEFT JOIN $editors ".
        "ON (CONVERT($users.username USING utf8)=CONVERT($editors.name USING utf8)) WHERE $editors.name IS NULL AND $users.active='1'";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $users = array();
    foreach ($result as $user) {
      $add = array();
      foreach ($user as $key => $value)
        $add[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;
      $users[] = $add;
    }
    return $users;
  } // selectAsEditorAvailableUsers()

  /**
   * Select the email of the given editor ID
   *
   * @param integer $editor_id
   * @return boolean|string
   */
  public function selectEMailByEditorId($editor_id) {
    global $db;

    $users = CMS_TABLE_PREFIX.'users';
    $editors = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';

    try {
      $SQL = "SELECT `email` FROM $users LEFT JOIN $editors ".
          "ON (CONVERT($users.username USING utf8)=CONVERT($editors.name USING utf8)) ".
          "WHERE $editors.id='$editor_id' AND $users.active='1'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['email'])) ? $result['email'] : '';
  } // selectEMailByEditorId()


  /**
   * Select the email of the given editor name
   *
   * @param string $editor_name
   * @return boolean|string
   */
  public function selectEMailByEditorName($editor_name) {
    global $db;

    $users = CMS_TABLE_PREFIX.'users';
    $editors = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';

    try {
      $SQL = "SELECT `email` FROM $users LEFT JOIN $editors ".
          "ON (CONVERT($users.username USING utf8)=CONVERT($editors.name USING utf8)) WHERE $editors.name='$editor_name' AND $users.active='1'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['email'])) ? $result['email'] : '';
  } // selectEMailByEditoName()

  /**
   * Select all Editors and return them in a array
   *
   * @return boolean|multitype:multitype:Ambigous <string, mixed, unknown>
   */
  public function selectAllEditors() {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `status`!='DELETED' ORDER BY `name` ASC";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $editors = array();
    foreach ($result as $editor) {
      $add = array();
      foreach ($editor as $key => $value)
        $add[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;
      $editors[] = $add;
    }
    return $editors;
  } // selectAllEditors()

  /**
   * Insert a new editor record into the table
   *
   * @param array $data
   * @param integer $editor_id as reference
   * @return boolean
   */
  public function insert($data, &$editor_id) {
    global $db;
    global $I18n;
    global $tools;

    $editor = array();
    foreach ($data as $key => $value)
      $editor[$key] = $tools->sanitizeVariable($value);

    try {
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team', $editor);
      $editor_id = $db->lastInsertId();
      $this->setInfo($I18n->translate('Insert a new editor with the ID {{ id }} into mod_wysiwyg_editor_team',
          array('id' => $editor_id)), __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insert()

  /**
   * Check if a position exists and is active
   *
   * @param string $position CHIEF_EDITOR, SUB_CHIEF_EDITOR, EDITOR, TRAINEE
   * @return boolean
   */
  public function existsPosition($position) {
    global $db;

    try {
      $SQL = "SELECT `id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `status`='ACTIVE' AND `position`='$position'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['id'])) ? true : false;
  } // existsChiefEditor()

  /**
   * Select available Supervisors
   *
   * @return boolean|multitype:multitype:Ambigous <string, mixed, unknown>
   */
  public function selectSupervisors() {
    global $db;
    global $tools;

    $users = CMS_TABLE_PREFIX.'users';
    $team = CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team';

    try {
      $SQL = "SELECT `id`, `user_id`, `username`, `display_name`  FROM `$users`, `$team` ".
        "WHERE CONVERT(username USING utf8)=CONVERT(name USING utf8) AND `status`='ACTIVE' AND `position`!='TRAINEE'";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $editors = array();
    foreach ($result as $editor) {
      $add = array();
      foreach ($editor as $key => $value)
        $add[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;
      $editors[] = $add;
    }
    return $editors;
  } // selectSupervisors

  /**
   * Check if the desired permission is grant by the user rights
   *
   * @param integer $rights of the user
   * @param integer $permission to check
   * @return boolean
   */
  public static function checkPermission($rights, $permission) {
    return ($rights & $permission) ? true : false;
  } // checkPermissions()

  /**
   * Select the name of the chief editor.
   * Return an empty string if no chief editor exists
   *
   * @return boolean|string
   */
  public function selectChiefEditorName() {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT `name` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `status`='ACTIVE' AND `position`='CHIEF_EDITOR'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['name'])) ? $tools->unsanitizeText($result['name']) : '';
  } // selectChiefEditorName()

  /**
   * Select an editor by the given name and return the complete data record
   *
   * @param string $name
   * @return boolean|multitype:Ambigous <string, mixed, unknown>
   */
  public function selectEditorByName($name) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `name`='$name'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    $editor = array();
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $editor[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;
    }
    return $editor;
  } // selectEditorByName()

  /**
   * Select an editor by the given ID and return the complete data record
   *
   * @param integer $id
   * @return boolean|multitype:Ambigous <string, mixed, unknown>
   */
  public function selectEditorById($id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `id`='$id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    $editor = array();
    foreach ($result as $key => $value)
      $editor[$key] = is_string($value) ? $tools->unsanitizeText($value) : $value;

    return $editor;
  } // selectEditorById()
  /**
   * Delete an editor record by name
   *
   * @param string $name
   * @return boolean
   */
  public function deleteByName($name) {
    global $db;
    global $I18n;

    try {
      $db->delete(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team', array('name' => $name));
      $this->setInfo($I18n->translate('Editor {{ name }} successfull deleted.',
          array('name' => $name)), __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // deleteByName()

  /**
   * Update the given editor team record
   *
   * @param integer $id
   * @param array $data
   * @return boolean
   */
  public function update($id, $data) {
    global $db;
    global $tools;

    $update = array();
    foreach ($data as $key => $value)
      $update[$key] = (is_string($value)) ? $tools->sanitizeVariable($value) : $value;

    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg_editor_team', $update, array('id' => $id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // update()


  /**
   * Select the editors assigned to the given department. Return an array with
   * the editor IDs
   *
   * @param integer $department_id
   * @param boolean $active_only return only editors with status 'active'
   * @return boolean|multitype:unknown
   */
  public function selectEditorsOfDepartment($department_id, $active_only=true) {
    global $db;

    try {
      $SQL = "SELECT `id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE (`departments`='$department_id' OR ".
          "(`departments` LIKE '$department_id,%') OR (`departments` LIKE '%,$department_id,%') OR ".
          "(`departments` LIKE '%,$department_id'))";
      if ($active_only)
        $SQL .= " AND `status`='ACTIVE'";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $editors = array();
    if (is_array($result)) {
      foreach ($result as $editor)
        $editors[] = $editor['id'];
    }
    return $editors;
  } // selectEditorsOfDepartment()

  /**
   * Check if the given editor name is a chief editor or a sub chief editor
   *
   * @param string $name
   * @return boolean
   */
  public function isChiefEditor($name) {
    global $db;

    try {
      $SQL = "SELECT `name` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE `name`='$name' AND ".
          "`status`='ACTIVE' AND (`position`='CHIEF_EDITOR' OR `position`='SUB_CHIEF_EDITOR')";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return (isset($result['name'])) ? true : false;
  } // isChiefEditor()

  /**
   * Check if $name is editor for the department $department_id.
   *
   * @param integer $department_id
   * @param string $name
   * @param array $editor reference, contains the editor record
   * @return boolean
   */
  public function isEditorForDepartment($department_id, $name, &$editor=array()) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_editor_team` WHERE (`departments`='$department_id' OR ".
          "(`departments` LIKE '$department_id,%') OR (`departments` LIKE '%,$department_id,%') OR ".
          "(`departments` LIKE '%,$department_id')) AND `name`='$name' AND `status`='ACTIVE'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $editor = array();
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $editor[$key] = (is_string($value)) ? $tools->unsanitizeText($value) : $value;
    }
    return (count($editor) > 0) ? true : false;
  } // isEditorForDepartment()

} // class editorTeam
