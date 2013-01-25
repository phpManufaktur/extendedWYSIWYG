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

class addonVersion extends boneClass {

  public function get() {
    global $I18n;

    // read info.php into array
    $info_text = file(CMS_ADDON_PATH.'/info.php');
    if ($info_text == false) {
      $this->setMessage($I18n->translate('<p>Can\'t detect the version number of the addon!</p>'), __METHOD__, __LINE__);
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    $this->setMessage($I18n->translate('<p>Can\'t detect the version number of the addon!</p>'), __METHOD__, __LINE__);
    return -1;
  } // get()

} // class addonVersion
