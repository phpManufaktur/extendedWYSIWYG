<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Service\View;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\CMS\Bridge\cmsBridge;

global $logger;
global $tools;
global $db;
global $I18n;

class Service extends boneClass {

  public function exec() {
    global $I18n;

    echo $I18n->translate('Chars');
    echo __METHOD__;
  } // exec()

} // class Service