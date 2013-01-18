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

global $logger;
global $tools;

class View extends boneClass {

  /**
   * Get the content for the $section_id and return it
   *
   * @return string
   */
  public function getSection($section_id) {
    global $db;
    global $tools;

    $section_id = (int) $section_id;
    if ($section_id < 1) {
      // invalid section id!
      $this->setError('The submitted Section ID is invalid', __METHOD__, __LINE__);
      return $this->getError();
    }
    try {
      $SQL = "SELECT content FROM `".CMS_TABLE_PREFIX."mod_wysiwyg` WHERE section_id=?";
      $section = $db->fetchAssoc($SQL, array($section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }

    $section = $tools->unsanitizeText($section['content']);
    if ((CMS_TYPE == 'WebsiteBaker') && (CMS_VERSION == '2.8.1')) {
      // in WB 2.8.1 the [wblinks ...] must be preprocessed separate
      global $wb;
      $wb->preprocess($section);
    }
    return $section;
  } // getSection()

} // class View
