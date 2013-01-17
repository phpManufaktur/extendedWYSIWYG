<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
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
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`extension_id`), ".
    "KEY (`section_id`, `page_id`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// create the TEASER table
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wysiwyg_teaser` ( ".
    "`teaser_id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`page_id` INT(11) NOT NULL DEFAULT '0', ".
    "`teaser_text` TEXT NOT NULL DEFAULT '', ".
    "`hash` VARCHAR(32) NOT NULL DEFAULT '', ".
    "`author` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`date_publish` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ".
    "`status` ENUM('ACTIVE','UNPUBLISHED','BACKUP') NOT NULL DEFAULT 'ACTIVE', ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`teaser_id`), ".
    "KEY (`page_id`, `status`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// install or upgrade droplets
if (file_exists(WB_PATH.'/modules/droplets/functions.inc.php')) {
  include_once(WB_PATH.'/modules/droplets/functions.inc.php');
}

if (!function_exists('wb_unpack_and_import')) {
  function wb_unpack_and_import($temp_file, $temp_unzip) {
    global $admin, $database;

    // Include the PclZip class file
    require_once (WB_PATH . '/include/pclzip/pclzip.lib.php');

    $errors = array();
    $count = 0;
    $archive = new PclZip($temp_file);
    $list = $archive->extract(PCLZIP_OPT_PATH, $temp_unzip);
    // now, open all *.php files and search for the header;
    // an exported droplet starts with "//:"
    if (false !== ($dh = opendir($temp_unzip))) {
      while (false !== ($file = readdir($dh))) {
        if ($file != "." && $file != "..") {
          if (preg_match('/^(.*)\.php$/i', $file, $name_match)) {
            // Name of the Droplet = Filename
            $name = $name_match[1];
            // Slurp file contents
            $lines = file($temp_unzip . '/' . $file);
            // First line: Description
            if (preg_match('#^//\:(.*)$#', $lines[0], $match)) {
              $description = $match[1];
            }
            // Second line: Usage instructions
            if (preg_match('#^//\:(.*)$#', $lines[1], $match)) {
              $usage = addslashes($match[1]);
            }
            // Remaining: Droplet code
            $code = implode('', array_slice($lines, 2));
            // replace 'evil' chars in code
            $tags = array(
                '<?php',
                '?>',
                '<?'
            );
            $code = addslashes(str_replace($tags, '', $code));
            // Already in the DB?
            $stmt = 'INSERT';
            $id = NULL;
            $found = $database->get_one("SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE name='$name'");
            if ($found && $found > 0) {
              $stmt = 'REPLACE';
              $id = $found;
            }
            // execute
            $result = $database->query("$stmt INTO " . TABLE_PREFIX . "mod_droplets VALUES('$id','$name','$code','$description','" . time() . "','" . $admin->get_user_id() . "',1,0,0,0,'$usage')");
            if (!$database->is_error()) {
              $count++;
              $imports[$name] = 1;
            }
            else {
              $errors[$name] = $database->get_error();
            }
          }
        }
      }
      closedir($dh);
    }
    return array(
        'count' => $count,
        'errors' => $errors,
        'imported' => $imports
    );
  } // function wb_unpack_and_import()
}
// install the droplet(s)
wb_unpack_and_import(WB_PATH.'/modules/wysiwyg/droplets/droplet_wysiwyg_teaser.zip', WB_PATH . '/temp/unzip/');

require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';

// initialize the configuration
$config = new manufakturConfig();
if (!$config->readXMLfile(LEPTON_PATH.'/modules/wysiwyg/config/extendedWYSIWYG.xml', 'wysiwyg', true)) {
  $admin->print_error($config->getError());
}



