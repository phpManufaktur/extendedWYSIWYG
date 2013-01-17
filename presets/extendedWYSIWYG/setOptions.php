<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
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

if (!isset($_GET['page_id']))
  exit('Missing the parameter page_id!');
if (!isset($_GET['section_id']))
  exit('Missing the parameter section_id!');
if (!isset($_GET['options']))
  exit('Missing the parameter options!');

$page_id = (int) $_GET['page_id'];
$section_id = (int) $_GET['section_id'];
$options = $_GET['options'];

// check if an extension entry exists
$SQL = "SELECT `options` FROM `".TABLE_PREFIX."mod_wysiwyg_extension` WHERE `page_id`='$page_id' AND `section_id`='$section_id'";
$query = $database->query($SQL);
if ($database->is_error())
  exit($database->get_error());
if ($query->numRows() < 1) {
  // insert a new record
  $SQL = "INSERT INTO `".TABLE_PREFIX."mod_wysiwyg_extension` (`section_id`, `page_id`) VALUES ('$section_id','$page_id')";
  $database->query($SQL);
  if ($database->is_error())
    exit($database->get_error());
}
// update the record
$SQL = "UPDATE `".TABLE_PREFIX."mod_wysiwyg_extension` SET `options`='$options' WHERE `section_id`='$section_id'";
$database->query($SQL);
if ($database->is_error())
  exit($database->get_error());
exit('OK');
