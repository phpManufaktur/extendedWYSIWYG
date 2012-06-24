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

$SQL = "CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_wysiwyg` ( ".
  "`section_id` INT NOT NULL DEFAULT '0', ".
  "`page_id` INT NOT NULL DEFAULT '0', ".
  "`content` LONGTEXT NOT NULL , ".
  "`text` LONGTEXT NOT NULL , ".
  "`hash` VARCHAR(32) NOT NULL DEFAULT '', ".
  "`timestamp` TIMESTAMP, ".
  "PRIMARY KEY (`section_id`) ".
  ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

