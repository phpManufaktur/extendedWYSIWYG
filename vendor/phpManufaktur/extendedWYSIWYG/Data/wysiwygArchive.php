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
      `editor` VARCHAR(255) NOT NULL DEFAULT '',
      `supervisors` VARCHAR(255) NOT NULL DEFAULT '',
      `approved` VARCHAR(255) NOT NULL DEFAULT '',
      `refused` VARCHAR(255) NOT NULL DEFAULT '',
      `status` ENUM('ACTIVE','UNPUBLISHED','BACKUP','DRAFT','PENDING') NOT NULL DEFAULT 'ACTIVE',
      `publish` ENUM('PRIVATE','APPROVAL','PROOFREAD','REFUSED','PUBLISHED') NOT NULL DEFAULT 'PRIVATE',
      `deadline` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
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
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` WHERE `archive_id`=:archive_id";
      $result = $db->fetchAssoc($SQL, array('archive_id' => $archive_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $archive = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $archive[$key] = $tools->unsanitizeText($value);
    }

    // replace placeholders with the actual MEDIA URL
    if (isset($archive['content']))
      $archive['content'] = str_replace('~~ wysiwyg replace[CMS_MEDIA_URL] ~~', CMS_MEDIA_URL, $archive['content']);

    return $archive;
  } // select()

  /**
   * Select the last Archive record for the given SECTION ID and return a array
   *
   * @param integer $section_id
   * @return boolean|multitype:
   */
  public function selectLast($section_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` WHERE `section_id`=:section_id ORDER BY `archive_id` DESC LIMIT 1";
      $result = $db->fetchAssoc($SQL, array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $archive = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $archive[$key] = $tools->unsanitizeText($value);
    }

    // replace placeholders with the actual MEDIA URL
    if (isset($archive['content']))
      $archive['content'] = str_replace('~~ wysiwyg replace[CMS_MEDIA_URL] ~~', CMS_MEDIA_URL, $archive['content']);

    return $archive;
  } // selectLast()

  /**
   * Select the last ACTIVE Archive record for the given SECTION ID and return a array
   *
   * @param integer $section_id
   * @return boolean|multitype:
   */
  public function selectLastActive($section_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` WHERE `section_id`=:section_id AND `status`='ACTIVE' ORDER BY `archive_id` DESC LIMIT 1";
      $result = $db->fetchAssoc($SQL, array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $archive = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $archive[$key] = $tools->unsanitizeText($value);
    }

    // replace placeholders with the actual MEDIA URL
    if (isset($archive['content']))
      $archive['content'] = str_replace('~~ wysiwyg replace[CMS_MEDIA_URL] ~~', CMS_MEDIA_URL, $archive['content']);

    return $archive;
  } // selectLastActive()

  /**
   * Check if the archive for the section is pending
   *
   * @param integer $section_id
   * @param array $archive
   * @return boolean
   */
  public function isPending($section_id, &$archive=array()) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` WHERE `section_id`=:section_id AND `status`='PENDING' ORDER BY `archive_id` DESC LIMIT 1";
      $result = $db->fetchAssoc($SQL, array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $archive = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $archive[$key] = $tools->unsanitizeText($value);
    }
    // replace placeholders with the actual MEDIA URL
    if (isset($archive['content']))
      $archive['content'] = str_replace('~~ wysiwyg replace[CMS_MEDIA_URL] ~~', CMS_MEDIA_URL, $archive['content']);

    return (count($archive) > 0) ? true : false;
  } // isPending()

  /**
   * Insert a new ARCHIVE record from the given SECTION content
   *
   * @param integer $page_id
   * @param integer $section_id
   * @param string $content
   * @param string $author
   * @param integer $archive_id REFERENCE
   * @return boolean
   */
  public function insert($page_id, $section_id, $content, $author, $status, &$archive_id=-1) {
    global $db;
    global $tools;
    global $I18n;

    try {
      // set all previous archive entries with ACTIVE status to BACKUP status
      $SQL = "UPDATE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` SET `status`='BACKUP' ".
        "WHERE `section_id`='$section_id' AND (`status`='ACTIVE' OR `status`='')";
      $db->query($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    // get md5 before replacing anything!
    $md5 = md5($content);

    $config = new wysiwygConfiguration();
    $use_relative_url = $config->getValue('cfgUseRelativeMediaURL');
    if ($config->isError()) {
      $this->setError($config->getMessage(), __METHOD__, __LINE__);
      return false;
    }
    if ($use_relative_url) {
      // replace absolute URLs
      $searchfor = '@(<[^>]*=\s*")('.preg_quote(CMS_MEDIA_URL).')([^">]*".*>)@siU';
      $content = preg_replace($searchfor, '$1~~ wysiwyg replace[CMS_MEDIA_URL] ~~$3', $content);
    }

    try {
      // prepare the data array
      $data = array(
          'page_id' => (int) $page_id,
          'section_id' => (int) $section_id,
          'content' => $tools->sanitizeText($content),
          'hash' => $md5,
          'author' => $author,
          'status' => $status
          );
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', $data);
      $archive_id = $db->lastInsertId();
      $this->setInfo($I18n->translate('Insert record with the ARCHIVE ID {{ archive_id }} from content of the SECTION ID {{ section_id }}',
          array('archive_id' => $archive_id, 'section_id' => $section_id)), __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insert()

  /**
   * Insert a new archive record as editorial record
   *
   * @param array $draft_data
   * @param integer $archive_id reference to the new archive id
   * @return boolean
   */
  public function insertEditorial($draft_data, &$archive_id=-1) {
    global $db;
    global $I18n;
    global $tools;

    // get md5 before replacing anything!
    $md5 = md5($draft_data['content']);

    // check if we have to use relative media urls
    $config = new wysiwygConfiguration();
    $use_relative_url = $config->getValue('cfgUseRelativeMediaURL');
    if ($config->isError()) {
      $this->setError($config->getMessage(), __METHOD__, __LINE__);
      return false;
    }
    if ($use_relative_url) {
      // replace absolute URLs
      $searchfor = '@(<[^>]*=\s*")('.preg_quote(CMS_MEDIA_URL).')([^">]*".*>)@siU';
      $content = preg_replace($searchfor, '$1~~ wysiwyg replace[CMS_MEDIA_URL] ~~$3', $draft_data['content']);
    }

    try {
      // prepare the data array
      $data = array(
          'page_id' => (int) $draft_data['page_id'],
          'section_id' => (int) $draft_data['section_id'],
          'content' => $tools->sanitizeText($draft_data['content']),
          'hash' => $md5,
          'author' => $tools->sanitizeText($draft_data['author']),
          'status' => isset($draft_data['status']) ? $draft_data['status'] : 'DRAFT',
          'editor' => $draft_data['editor'],
          'publish' => isset($draft_data['publish']) ? $draft_data['publish'] : 'PRIVATE',
          'supervisors' => isset($draft_data['supervisors']) ? $draft_data['supervisors'] : ''
      );
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', $data);
      $archive_id = $db->lastInsertId();
      $this->setInfo($I18n->translate('Editor {{ editor }} saved a DRAFT with the ARCHIVE ID {{ archive_id }} for the SECTION ID {{ section_id }}.',
          array('archive_id' => $archive_id, 'section_id' => $draft_data['section_id'], 'editor' => $draft_data['editor'])),
          __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insertDraft()


  /**
   * Get the last Archive entries for the selection in the dialog.
   * Limit will be set by the configuration value, return a associative array.
   *
   * @param integer $section_id
   * @return boolean|multitype:
   */
  public function selectArchiveListForDialog($section_id) {
    global $db;

    $configuration = new wysiwygConfiguration();
    if (false === ($limit = (int) $configuration->getValue('cfgArchiveIdSelectLimit'))) {
      $this->setError($configuration->getError(), __METHOD__, __LINE__);
      return false;
    }

    try {
      // select only archives of status ACTIVE, UNPUBLISHED or BACKUP
      $SQL = "SELECT `timestamp`, `status`, `archive_id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ".
        "WHERE `section_id`='$section_id' AND (`status`='ACTIVE' OR `status`='UNPUBLISHED' OR ".
        "`status`='BACKUP') ORDER BY `archive_id` DESC LIMIT $limit";
      $archives = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return $archives;
  } // selectArchiveListForDialog()

  /**
   * Select the archive list for an editor in the active editorial system
   *
   * @param integer $section_id
   * @param string $editor_name
   * @return boolean|multitype:unknown multitype:
   */
  public function selectEditorialArchiveListForDialog($section_id, $editor_name) {
    global $db;

    $configuration = new wysiwygConfiguration();
    if (false === ($limit = (int) $configuration->getValue('cfgArchiveIdSelectLimit'))) {
      $this->setError($configuration->getError(), __METHOD__, __LINE__);
      return false;
    }

    try {
      // first we get the active content
      $SQL = "SELECT `timestamp`, `status`, `archive_id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ".
          "WHERE `section_id`='$section_id' AND `status`='ACTIVE' ORDER BY `archive_id` DESC LIMIT 1";
      $active = $db->fetchAssoc($SQL);
      $SQL = "SELECT `timestamp`, `status`, `archive_id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ".
          "WHERE `section_id`='$section_id' AND `status`='DRAFT' AND `editor`='$editor_name' ORDER BY `archive_id` DESC LIMIT $limit";
      $drafts = $db->fetchAll($SQL);
      // now get the drafts
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $list = array();
    if (is_array($active)) $list[$active['archive_id']] = $active;
    if (is_array($drafts)) {
      foreach ($drafts as $draft)
        $list[$draft['archive_id']] = $draft;
    }
    krsort($list);
    return $list;
  } // selectEditorialArchiveListForDialog()

  /**
   * Update the archive with the given ID
   *
   * @param integer $archive_id
   * @param array $archive_data
   * @return boolean
   */
  public function update($archive_id, $archive_data) {
    global $db;
    global $tools;
    global $I18n;

    $data = array();
    foreach ($archive_data as $key => $value)
      $data[$key] = (is_string($value)) ? $tools->unsanitizeText($value) : $value;

    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', $data, array('archive_id' => $archive_id));
      $this->setInfo($I18n->translate('Archive ID {{ archive_id }} successfull updated.',
          array('archive_id' => $archive_id)), __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // update()

} // class wysiwygArchive
