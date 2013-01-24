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

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\View;

class modifySection extends boneClass {

  const REQUEST_ACTION = 'act';
  const REQUEST_ARCHIVE_ID = 'archive_id';
  const REQUEST_TEASER_ID = 'teaser_id';

  const ACTION_MODIFY = 'mod';

  protected static $SECTION_ID = null;
  protected static $PAGE_ID = null;
  protected static $ARCHIVE_ID = null;
  protected static $TEASER_ID = null;

  public function __construct($page_id, $section_id) {
    $this->setInfo('Initialize class modifySection', __METHOD__, __LINE__);
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
  } // __construct()

  /**
   * Action handler for class modifySection
   *
   * @return string modify dialog
   */
  public function action() {

    // set requested action or default $command
    $action = (isset($_REQUEST[self::REQUEST_ACTION])) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_MODIFY;

    switch ($action):

    case self::ACTION_MODIFY:
    default:
      $modify = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
      if (isset($_REQUEST[self::REQUEST_ARCHIVE_ID.self::$SECTION_ID])) {
        // set the ARCHIVE ID
        self::$ARCHIVE_ID = (int) $_REQUEST[self::REQUEST_ARCHIVE_ID.self::$SECTION_ID];
        $modify->setArchiveID(self::$ARCHIVE_ID);
      }
      if (isset($_REQUEST[self::REQUEST_TEASER_ID])) {
        self::$TEASER_ID = (int) $_REQUEST[self::REQUEST_TEASER_ID];
        $modify->setTeaserID(self::$TEASER_ID);
      }
      $content = $modify->view();
    endswitch;

    return $content;
  } // view()

} // class modifySection