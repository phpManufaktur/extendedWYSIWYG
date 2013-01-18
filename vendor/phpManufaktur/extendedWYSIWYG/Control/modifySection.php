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

  protected static $SECTION_ID = null;
  protected static $PAGE_ID = null;

  public function __construct($page_id, $section_id) {
    $this->setInfo('Construct class modifySection', __METHOD__, __LINE__);
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
  } // __construct()

  public function action() {

    $modify = new View\modifySection();
    return $modify->view();
  } // view()

} // class modifySection