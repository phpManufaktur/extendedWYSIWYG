<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file will be called by jQuery placed at the section editing page.
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;

$path = __DIR__;
for ($i=0; $i < 10; $i++) {
  // try to find and load the bootstrap.php
  if (@file_exists($path.'/bootstrap.php')) {
    define('EXTERNAL_ACCESS', false);
    include $path.'/bootstrap.php';
    break;
  }
  $path = substr($path, 0, strrpos($path, '/'));
}

global $I18n;

// check the needed parameters
if (!isset($_REQUEST['section_id']) ||
    !isset($_REQUEST['section_content'])
    ) {
  echo 'Invalid parameters';
}
else {
  $section_id = (int) $_REQUEST['section_id'];
  $section_content = rawurldecode($_REQUEST['section_content']);
  $section = new wysiwygSection();
  if (!$section->update($section_id, $section_content))
    echo $section->getError();
  else
    echo $I18n->translate('Chars');
}