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

// update the module name = extendedWYSIWYG
$SQL = "UPDATE `".TABLE_PREFIX."addons` SET `name`='extendedWYSIWYG' WHERE `directory`='wysiwyg'";
if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// we have to delete some files and directories from the origin installation
$language_files = array('DA.php','FR.php','NL.php','NO.php','RU.php');
foreach ($language_files as $file)
  @unlink(LEPTON_PATH.'/modules/wysiwyg/languages/'.$file);

// WYSIWYG of LEPTON have a directory '/classes' which is no longer needed
rrmdir(LEPTON_PATH.'/modules/wysiwyg/classes');

// now create the WYSIWYG archive table
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wysiwyg_archive` ( ".
    "`archive_id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`section_id` INT(11) NOT NULL DEFAULT '0', ".
    "`page_id` INT(11) NOT NULL DEFAULT '0', ".
    "`content` LONGTEXT NOT NULL, ".
    "`hash` VARCHAR(32) NOT NULL DEFAULT '', ".
    "`remark` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`author` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`status` ENUM('ACTIVE','UNPUBLISHED','BACKUP') NOT NULL DEFAULT 'ACTIVE', ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`archive_id`), ".
    "KEY (`section_id`, `page_id`, `status`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// create the WYSIWYG extension table
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wysiwyg_extension` ( ".
    "`extension_id` INT (11) NOT NULL AUTO_INCREMENT, ".
    "`section_id` INT(11) NOT NULL DEFAULT '0', ".
    "`page_id` INT(11) NOT NULL DEFAULT '0', ".
    "`options` INT(11) NOT NULL DEFAULT '0', ".
    "`teaser_text` TEXT NOT NULL, ".
    "`teaser_image` TEXT NOT NULL, ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`extension_id`), ".
    "KEY (`section_id`, `page_id`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';

// initialize the configuration
$config = new manufakturConfig();
if (!$config->readXMLfile(LEPTON_PATH.'/modules/wysiwyg/config/extendedWYSIWYG.xml', 'wysiwyg', true)) {
  $admin->print_error($config->getError());
}

