<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;

use phpManufaktur\CMS\Bridge\Control\boneClass;

class deleteSection extends boneClass {

  protected static $PAGE_ID = null;
  protected static $SECTION_ID = null;

  /**
   * Constructor for class deleteSection
   *
   * @param integer $page_id
   * @param integer $section_id
   */
  public function __construct($page_id, $section_id) {
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
  }

  /**
   * Check the permissions and delete the section
   *
   * @return boolean
   */
  public function exec() {
    global $I18n;

    $controlAccess = new controlAccess(self::$PAGE_ID, self::$SECTION_ID);
    if (!$controlAccess->checkSectionDelete()) {
      $this->setError($I18n->translate('<p>You are not allowed to delete the section with the ID {{ section_id }}!</p>',
          array('section_id' => self::$SECTION_ID)), __METHOD__, __LINE__);
      return false;
    }

    $wysiwygSection = new wysiwygSection();
    if (!$wysiwygSection->delete(self::$SECTION_ID)) {
      $this->setError($wysiwygSection->getError(), __METHOD__, __LINE__);
      return false;
    }
    return true;

  } // exec()

} // class deleteSection