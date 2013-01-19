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


class wysiwygOptions extends boneClass {

  /**
   * Return a array with the options for the specified SECTION ID
   *
   * @param integer $section_id
   * @return array
   */
  public function selectArray($section_id) {
    global $db;

    try {
      $SQL = "SELECT options FROM ".CMS_TABLE_PREFIX."mod_wysiwyg_extension WHERE section_id=?";
      $options_string = $db->fetchAssoc($SQL, array($section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    return explode(',', $options_string['options']);
  } // getOptionArray()
}