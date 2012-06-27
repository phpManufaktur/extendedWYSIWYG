<?php

/**
 * extendedWYSIWYG
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

function Dwoo_Plugin_show_wysiwyg_editor(Dwoo $dwoo, $name, $id, $content, $width='100%', $height='350px') {
  global $wysiwyg_editor_loaded;

  if (!function_exists('show_wysiwyg_editor')) {
    if (!defined('WYSIWYG_EDITOR') || (WYSIWYG_EDITOR == 'none') ||
        !file_exists(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
      // no WYSIWYG editor available so use a textarea instead
      $content = sprintf('<textarea name="%s" id="%s" style="width: %s; height: %s;">%s</textarea>',
          $name, $id, $width, $height, $content);
    }
    else {
      // load WYSIWYG editor
      require_once(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
      ob_start();
      show_wysiwyg_editor($name, $id, $content, $width, $height);
      $content = ob_get_clean();
    }
    $wysiwyg_editor_loaded = true;
  }
  else {
    ob_start();
    show_wysiwyg_editor($name, $id, $content, $width, $height);
    $content = ob_get_clean();
  }
  echo $content;
} // Dwoo_Plugin_show_wysiwyg_editor()
