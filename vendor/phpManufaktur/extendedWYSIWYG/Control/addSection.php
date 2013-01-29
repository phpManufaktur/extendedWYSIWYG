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

use phpManufaktur\CMS\Bridge\Data\LEPTON;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;
use phpManufaktur\CMS\Bridge\Control\boneClass;

class addSection extends boneClass {

  protected static $PAGE_ID = null;
  protected static $SECTION_ID = null;

  /**
   * Constructor for class addSection
   *
   * @param integer $page_id
   * @param integer $section_id
   */
  public function __construct($page_id, $section_id) {
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
  }

  /**
   * Check the permissions and add a blank section
   *
   * @return boolean
   */
  public function exec() {
    global $I18n;

    $controlAccess = new controlAccess(self::$PAGE_ID, self::$SECTION_ID);
    if (!$controlAccess->checkSectionAdd()) {
      // problem: the CMS has already added a section with the given SECTION_ID!
      $Section = new LEPTON\Sections();
      // delete this ID from the sections table!
      if (!$Section->delete(self::$SECTION_ID)) {
        $this->setError($Section->getError(), __METHOD__, __LINE__);
        return false;
      }
      $this->setInfo($I18n->translate('Deleted the section with the ID {{ id }} from the sections table!',
          array('id' => self::$SECTION_ID)), __METHOD__, __LINE__);
      if (false === ($sections = $Section->selectAllSections(self::$PAGE_ID))) {
        $this->setError($Section->getError(), __METHOD__, __LINE__);
        return false;
      }
      if (count($sections) == 0) {
        // no further sections, so we can also delete the page!
        $Pages = new LEPTON\Pages();
        if (!$Pages->delete(self::$PAGE_ID)) {
          $this->setError($Pages->getError(), __METHOD__, __LINE__);
          return false;
        }
        $this->setInfo($I18n->translate('Deleted the page with the ID {{ id }} because it contains no further sections!',
            array('id' => self::$PAGE_ID)), __METHOD__, __LINE__);
      }
      $this->setError($I18n->translate('<p>You are not allowed to add/create a new section!</p>'), __METHOD__, __LINE__);
      return false;
    }

    // insert a blank wysiwyg section
    $wysiwygSection = new wysiwygSection();
    if (!$wysiwygSection->insertBlank(self::$PAGE_ID, self::$SECTION_ID)) {
      $this->setError($wysiwygSection->getError(), __METHOD__, __LINE__);
      return false;
    }
    return true;
  } // exec()

} // class deleteSection