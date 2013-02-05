<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace CKEditor;

use CKEditor\CKEditor_3\CKEditor;

class CKEditor extends \CKEditor  {

  public function getEditor($name, $content, $width='100%', $height='250px') {
    $this->basePath = CMS_PATH.'/vendor/';
  } // getEditor()

} // class CKEditor