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

use CKEditor\CKEditor3\Editor;

use CKEditor;

class wysiwygEditor extends boneClass {

  public function exec($name, $content, $width, $height, $toolbar) {
    $cke = new Editor();
    return $cke->exec($name, $content, $width, $height, $toolbar);
  } // exec()

} // class wysiwygEditor