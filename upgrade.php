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

global $database;
global $admin;

if (defined('LEPTON_VERSION'))
  $database->prompt_on_error(false);

/**
 * Check if the specified $field in table mod_wysiwyg exists
 *
 * @param string $field
 * @return boolean
 */
function fieldExists($field) {
  global $database;
  global $admin;
  if (null === ($query = $database->query("DESCRIBE `".TABLE_PREFIX."mod_wysiwyg`")))
    $admin->print_error($database->get_error());
  while (false !== ($data = $query->fetchRow(MYSQL_ASSOC)))
    if ($data['Field'] == $field) return true;
  return false;
} // sqlFieldExists()

/**
 * Delete a directory recursivly
 *
 * @param string $dir
 */
function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir")
          rrmdir($dir."/".$object);
        else
          unlink($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
} //rrmdir()

// exists the field 'hash'?
if (!fieldExists('hash')) {
  // add the field 'hash' to the table
  $SQL = "ALTER TABLE `".TABLE_PREFIX."mod_wysiwyg` ADD `hash` VARCHAR(32) NOT NULL DEFAULT '' AFTER `text`";
  if (!$database->query($SQL))
    $admin->print_error($database->get_error());
}

// exists the field 'timestamp'
if (!fieldExists('timestamp')) {
  // add the field 'timestamp' to the table
  $SQL = "ALTER TABLE `".TABLE_PREFIX."mod_wysiwyg` ADD `timestamp` TIMESTAMP AFTER `hash`";
  if (!$database->query($SQL))
    $admin->print_error($database->get_error());
}

// update the module name
$SQL = "UPDATE `".TABLE_PREFIX."addons` SET `name`='extendedWYSIWYG' WHERE `directory`='wysiwyg'";
if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// we have to delete some files and directories
$language_files = array('DA.php','FR.php','NL.php','NO.php','RU.php');
foreach ($language_files as $file)
  @unlink(WB_PATH.'/modules/wysiwyg/languages/'.$file);

// WYSIWYG of LEPTON have a directory '/classes' which is not needed
rrmdir(WB_PATH.'/modules/wysiwyg/classes');