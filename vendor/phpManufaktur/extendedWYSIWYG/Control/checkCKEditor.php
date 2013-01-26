<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file will be called by jQuery placed at the section editing page.
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\CMS\Bridge\Data\LEPTON as LEPTON;
use phpManufaktur\CMS\Bridge\Data\WebsiteBaker as WebsiteBaker;
use phpManufaktur\CMS\Bridge\Control\boneClass;

class checkCKEditor extends boneClass {

  /**
   * Check if the CKEditor is installed
   *
   * @return boolean
   */
  public function isInstalled() {
    return file_exists(CMS_PATH.'/modules/ckeditor/info.php');
  } // isInstalled()

  /**
   * Check if the CKEditor is the active editor for the CMS
   *
   * @return boolean
   */
  public function isActiveEditor() {
    if (CMS_TYPE == 'LEPTON')
      $setting = new LEPTON\Settings();
    else
      $setting = new WebsiteBaker\Setting();
    return ($setting->select('ckeditor') == 'ckeditor');
  } // isActiveEditor()

} // class checkCKEactive