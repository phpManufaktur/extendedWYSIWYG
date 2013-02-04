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

class boneModifySection extends boneClass {

  const ANCHOR = 'wysiwyg_';

  const REQUEST_ACTION = 'act';
  const REQUEST_ARCHIVE_ID = 'archive_id';
  const REQUEST_TEASER_CONTENT = 'teaser_text';
  const REQUEST_TEASER_ID = 'teaser_id';
  const REQUEST_EDITORIAL_SYSTEM = 'editorial_system';
  const REQUEST_EDITOR_NAME = 'editor_name';
  const REQUEST_APPROVAL = 'approval';
  const REQUEST_EMAIL_TEXT = 'email_text';
  const REQUEST_EMAIL_SEND = 'email_send';
  const REQUEST_EDITOR_ACTION = 'editor_action';
  const REQUEST_EDITOR_RESPONSE = 'editor_response';

  const ACTION_MODIFY = 'mod';

  protected static $ARCHIVE_ID = null;
  protected static $MODIFY_PAGE_URL = null;
  protected static $PAGE_ID = null;
  protected static $SECTION_ANCHOR = null;
  protected static $SECTION_ID = null;
  protected static $SECTION_POSITION = null;
  protected static $SECTION_IS_FIRST = null;
  protected static $TEASER_ID = null;
  protected static $TEMPLATE_PATH = null;


  protected $lang = null;

  public function __construct($page_id, $section_id) {
    global $I18n;

    self::$TEMPLATE_PATH = CMS_ADDON_PATH.'/vendor/phpManufaktur/extendedWYSIWYG/View/Templates/Backend/';
    $this->lang = $I18n;
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
    self::$SECTION_ANCHOR = self::ANCHOR.self::$SECTION_ID;
    self::$MODIFY_PAGE_URL = CMS_ADMIN_URL.'/pages/modify.php';
  } // __construct()


} // class boneModifySection