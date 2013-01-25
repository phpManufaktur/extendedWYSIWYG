<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Control;

use phpManufaktur\kitCommand\kitCommand;

class outputFilter {

  public function exec($content) {
    $load_css = '';
    $load_js = array();
    $kitCommand = new kitCommand();
    $content = $kitCommand->Exec($content, $load_css, $load_js);

    return $content;
  }
} // class outputFilter
