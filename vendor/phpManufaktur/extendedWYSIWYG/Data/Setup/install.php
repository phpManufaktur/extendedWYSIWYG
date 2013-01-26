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

use phpManufaktur\extendedWYSIWYG\Data\editorDepartment;

use phpManufaktur\extendedWYSIWYG\Data\editorTeam;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygTeaser;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygExtension;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygArchive;
use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;

class install extends boneClass {

  /**
   * Change the regular addon name from 'wysiwyg' to 'extendedWYSIWYG'
   *
   * @return boolean
   */
  protected function changeAddonName() {
    global $db;

    try {
      $db->update(CMS_TABLE_PREFIX.'addons', array('name' => 'extendedWYSIWYG'),
          array('directory' => 'wysiwyg'));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // changeAddonName()

  protected function createTables() {
    // create table mod_wysiwyg
    $wysiwygSection = new wysiwygSection();
    if (!$wysiwygSection->create()) {
      $this->setError($wysiwygSection->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create table mod_wysiwyg_archive
    $wysiwygArchive = new wysiwygArchive();
    if (!$wysiwygArchive->create()) {
      $this->setError($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create table mod_wysiwyg_extension
    $wysiwygExtension = new wysiwygExtension();
    if (!$wysiwygExtension->create()) {
      $this->setError($wysiwygExtension->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create table mod_wysiwyg_teaser
    $wysiwygTeaser = new wysiwygTeaser();
    if (!$wysiwygTeaser->create()) {
      $this->setError($wysiwygTeaser->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create table mod_wysiwyg_editor_team
    $editorTeam = new editorTeam();
    if (!$editorTeam->create()) {
      $this->setError($editorTeam->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create table mod_wysiwyg_editor_department
    $editorDepartment = new editorDepartment();
    if (!$editorDepartment->create()) {
      $this->setError($editorDepartment->getError(), __METHOD__, __LINE__);
      return false;
    }

    return true;
  } // createTables()

  /**
   * Initialize the configuration for extendedWYSIWYG
   *
   * @return boolean
   */
  protected function initConfiguration($reset_values=false) {
    // initialize the configuration for extendedWYSIWYG
    $wysiwygConfiguration = new wysiwygConfiguration();
    $path = CMS_ADDON_CONFIG_PATH.'/extendedWYSIWYG.xml';
    if (!$wysiwygConfiguration->readXMLfile($path, 'wysiwyg', $reset_values)) {
      $this->setError($wysiwygConfiguration->getError(), __METHOD__, __LINE__);
      return false;
    }
    return true;
  } // initConfiguration()

  /**
   * Check if the WebsiteBaker output filter is already patched
   *
   * @param string $filter_path
   * @return boolean
   */
  protected function websiteBakerIsPatched($filter_path) {
    if (file_exists($filter_path)) {
      $lines = file($filter_path);
      foreach ($lines as $line)
        if (strpos($line, "extendedWYSIWYG") > 0) return true;
    }
    return false;
  } // websiteBakerIsPatched()

  /**
   * Patch the WebsiteBaker output filter
   *
   * @param string $filter_path
   * @param boolean $wb_283
   * @return boolean
   */
  protected function websiteBakerDoPatch($filter_path, $wb_283=false) {
    $search = $wb_283 ? "define('OUTPUT_FILTER_DOT_REPLACEMENT'" : 'function filter_frontend_output($content)';
    $returnvalue = false;
    $tempfile = CMS_PATH .'/modules/output_filter/new_filter.php';
    $backup = CMS_PATH .'/modules/output_filter/original-extended-wysiwyg-filter-routines.php';

    $addline = "\n\n\t\t// exec extendedWYSIWYG filter";
    $addline .= "\n\t\tif(file_exists(WB_PATH .'/modules/wysiwyg/vendor/phpManufaktur/CMS/Bridge/Control/outputFilter.php')) { ";
    $addline .= "\n\t\t\trequire_once (WB_PATH .'/modules/wysiwyg/vendor/phpManufaktur/CMS/Bridge/Control/outputFilter.php'); ";
    $addline .= "\n\t\t\t".'$content = cmsBridgeFilter($content); ';
    $addline .= "\n\t\t}\n\n ";

    if (file_exists($filter_path)) {
      $lines = file ($filter_path);
      $handle = fopen ($tempfile, 'w');
      foreach ($lines as $line) {
        fwrite ($handle, $line);
        if (strpos($line, $search) > 0) {
          $returnvalue = true;
          fwrite($handle, $addline);
        }
      }
      fclose ($handle);
      if (rename($filter_path, $backup)) {
        if (rename($tempfile, $filter_path)) {
          return $returnvalue;
        }
        else {
          return false;
        }
      }
    }
    return false;
  }

  /**
   * Add the output filter for extendedWYSIWYG
   *
   * @return boolean
   */
  protected function addFilter() {
    if (CMS_TYPE == 'LEPTON') {
      // register the filter at LEPTON outputInterface
      if (!file_exists(CMS_PATH .'/modules/output_interface/output_interface.php')) {
        $this->setError('Missing LEPTON outputInterface, can\'t register the cmsBridge filter - installation is not complete!',
            __METHOD__, __LINE__);
        return false;
      }
      else {
        if (!function_exists('register_output_filter'))
          include_once(CMS_PATH .'/modules/output_interface/output_interface.php');
        register_output_filter('wysiwyg', 'extendedWYSIWYG');
      }
    }
    else {
      if (version_compare(CMS_VERSION, '2.8.3', '>=')) {
        // WebsiteBaker 2.8.3
        $wb_283 = true;
        $filter_path = CMS_PATH.'/modules/output_filter/index.php';
      }
      else {
        // all other WebsiteBaker versions
        $wb_283 = false;
        $filter_path = CMS_PATH .'/modules/output_filter/filter-routines.php';
      }
      if (file_exists($filter_path)) {
        if (!$this->websiteBakerIsPatched($filter_path)) {
          if (!$this->websiteBakerDoPatch($filter_path, $wb_283)) {
            $this->setError('Failed to patch the WebsiteBaker output filter, please contact the support!', __METHOD__, __LINE__);
            return false;
          }
        }
      }
      else {
        $this->setError('Can\'t detect the correct method to patch the output filter, please contact the support!', __METHOD__, __LINE__);
        return false;
      }
    }
    return true;
  } // addFilter()

  /**
   * Execute the installation
   *
   * @return boolean
   */
  public function exec() {
    // change the addon name
    if (!$this->changeAddonName())
      return false;
    // create the tables
    if (!$this->createTables())
      return false;
    // initialize the configuration
    if (!$this->initConfiguration(true))
      return false;
    // add the output filter
    if (!$this->addFilter())
      return false;
    // install success!
    return true;
  } // exec()

} // class install
