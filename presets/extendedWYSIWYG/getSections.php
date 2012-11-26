<?php

/**
 * extendedWYSIWYG
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

$oneback = "../";
$root = '';
$level = 1;
while (($level < 10) && (!file_exists($root.'config.php'))) {
  $root .= $oneback;
  $level += 1;
}
if (file_exists($root.'config.php')) {
  require_once $root.'config.php';
}
else {
  trigger_error(sprintf("[ <b>%s</b> ] Can't find config.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

global $database;

if (!isset($_GET['page_id'])) {
  echo 'Missing the parameter page_id!';
}
$page_id = (int) $_GET['page_id'];
$SQL = "SELECT `section_id` FROM `".TABLE_PREFIX."sections` WHERE `page_id`='$page_id' AND `module`='wysiwyg' ORDER BY `position` ASC";
$sections = array();
$query = $database->query($SQL);
if ($database->is_error())
  exit('Error: '.$database->get_error());
while (false !== ($section = $query->fetchRow(MYSQL_ASSOC)))
  $sections[] = $section['section_id'];
$section_ids = implode(',', $sections);
exit($section_ids);