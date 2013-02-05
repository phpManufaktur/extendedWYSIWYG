<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\CMS\Bridge\Control\wysiwygEditor;

require_once CMS_ADDON_PATH.'/vendor/phpManufaktur/CMS/Bridge/Control/wysiwygEditor.php';

function Dwoo_Plugin_wysiwyg_editor(Dwoo $dwoo, $name, $content, $width='100%', $height='250px', $toolbar='default') {
  $editor = new wysiwygEditor();
  return $editor->exec($name, $content, $width, $height, $toolbar);
} // Dwoo_Plugin_wysiwygEditor()