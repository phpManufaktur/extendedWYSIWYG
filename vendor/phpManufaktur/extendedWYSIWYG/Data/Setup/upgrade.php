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