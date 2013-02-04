<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Data\Setup;

class upgrade extends install {

  /**
   * Check if the give column exists in the table
   *
   * @param string $table
   * @param string $column_name
   * @return boolean
   */
  protected function columnExists($table, $column_name) {
    global $db;

    try {
      $query = $db->query("DESCRIBE `$table`");
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    while (false !== ($row = $query->fetch())) {
      if ($row['Field'] == $column_name) return true;
    }
    return false;
  }

  /**
   * Process the upgrade especially for Release 11.01
   *
   * @return boolean
   */
  protected function release_1101() {
    global $tools;
    global $db;

    // some directories are no longer needed
    $delete_directories = array(
        '/languages',
        '/classes',
        '/templates',
        '/droplets',
        '/presets',
        '/templates',
        '/images',
        '/restore'
        );
    foreach ($delete_directories as $directory) {
      if (!$tools->deleteDirectory(CMS_ADDON_PATH.$directory))
        return false;
    }

    // delete no longer needed files
    $delete_files = array(
        '/save.php',
        '/backend.js',
        '/class.wysiwyg.php',
        '/wb2lepton.php',
        '/wysiwyg_teaser.css',
        '/custom.wysiwyg_teaser.css'
        );
    foreach ($delete_files as $file)
      @unlink(CMS_ADDON_PATH.$file);

    // the droplet wysiwyg_teaser is no longer supported
    try {
      $db->delete(CMS_TABLE_PREFIX.'mod_droplets', array('name' => 'wysiwyg_teaser'));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    // add entries to mod_wysiwyg_archive 'status'
    try {
      $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` MODIFY COLUMN status ENUM('ACTIVE','UNPUBLISHED','BACKUP','DRAFT','PENDING') NOT NULL DEFAULT 'ACTIVE'";
      $db->query($SQL);

      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'publish')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `publish` ENUM('PRIVATE','APPROVAL','PROOFREAD','REFUSED','PUBLISHED') NOT NULL DEFAULT 'PRIVATE' AFTER `status`";
        $db->query($SQL);
      }
      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'editor')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `editor` VARCHAR(255) NOT NULL DEFAULT '' AFTER `author`";
        $db->query($SQL);
      }
      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'supervisors')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `supervisors` VARCHAR(255) NOT NULL DEFAULT '' AFTER `editor`";
        $db->query($SQL);
      }
      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'approved')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `approved` VARCHAR(255) NOT NULL DEFAULT '' AFTER `supervisors`";
        $db->query($SQL);
      }
      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'refused')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `refused` VARCHAR(255) NOT NULL DEFAULT '' AFTER `approved`";
        $db->query($SQL);
      }
      if (!$this->columnExists(CMS_TABLE_PREFIX.'mod_wysiwyg_archive', 'deadline')) {
        $SQL = "ALTER TABLE `".CMS_TABLE_PREFIX."mod_wysiwyg_archive` ADD `deadline` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish`";
        $db->query($SQL);
      }

    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }

    return true;
  } // release_1101()

  /**
   * Exec the upgrade process for extendedWYSIWYG
   *
   * @see \phpManufaktur\extendedWYSIWYG\Data\Setup\install::exec()
   */
  public function exec() {
    // GIT files can be deleted
    @unlink(CMS_ADDON_PATH.'/.gitignore');
    @unlink(CMS_ADDON_PATH.'/gitattributes');

    // change the addon name
    if (!$this->changeAddonName())
      return false;

    // check if all tables are created
    if (!$this->createTables())
      return false;

    // initialize the configuration
    if (!$this->initConfiguration(false))
      return false;

    // add the output filter
    if (!$this->addFilter())
      return false;

    // Release 11.01
    if (!$this->release_1101())
      return false;

    // upgrade successfull
    return true;
  } // exec()

} // class install