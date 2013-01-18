<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Backend;

use phpManufaktur\CMS\Bridge\Control\boneClass;

global $db;

class Add extends boneClass {

  /**
   * Add a new, empty Section to the WYSIWYG table
   *
   * @param integer $page_id
   * @param integer $section_id
   * @return boolean
   */
  public function emptySection($page_id, $section_id) {
    global $db;

    try {
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg', array(
          'page_id' => (int) $page_id, 'section_id' => (int) $section_id));
      $this->setInfo("Added empty WYSIWYG Section for $page_id / $section_id", __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // emptySection()

} // class Add