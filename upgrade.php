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
if (defined('WB_PATH'))
  include __DIR__.'/bootstrap.php';

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
    "`options` VARCHAR(255) NOT NULL DEFAULT '', ".
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

// delete no longer needed files
@unlink(LEPTON_PATH.'/modules/wysiwyg/templates/backend/about.lte');
@unlink(LEPTON_PATH.'/modules/wysiwyg/templates/backend/archive_file.lte');
@unlink(LEPTON_PATH.'/modules/wysiwyg/templates/backend/body.lte');
@unlink(LEPTON_PATH.'/modules/wysiwyg/templates/backend/modify.lte');

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
@wb_unpack_and_import(WB_PATH.'/modules/wysiwyg/droplets/droplet_wysiwyg_teaser.zip', WB_PATH . '/temp/unzip/');

/**
 * RELEASE 11.01
 */

rrmdir(LEPTON_PATH.'/modules/wysiwyg/restore');

require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';

// initialize the configuration
$config = new manufakturConfig();
if (!$config->readXMLfile(LEPTON_PATH.'/modules/wysiwyg/config/extendedWYSIWYG.xml', 'wysiwyg', true)) {
  $admin->print_error($config->getError());
}

