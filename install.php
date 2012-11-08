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

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

if (defined('LEPTON_VERSION'))
  $database->prompt_on_error(false);

// create the regular WYSIWYG table without any changes
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wysiwyg` ( ".
  "`section_id` INT(11) NOT NULL DEFAULT '0', ".
  "`page_id` INT(11) NOT NULL DEFAULT '0', ".
  "`content` LONGTEXT NOT NULL, ".
  "`text` LONGTEXT NOT NULL, ".
  "PRIMARY KEY (`section_id`) ".
  ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// create the WYSIWYG archive table
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
    "`options` VARCHAR(255) NOT NULL DEFAULT '0', ".
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

