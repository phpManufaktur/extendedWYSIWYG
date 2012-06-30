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
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

$module_directory = 'wysiwyg';
$module_name = 'extendedWYSIWYG';
$module_function = 'page';
$module_version = '10.10';
$module_platform = '2.8';
$module_author = 'Ralf Hertsch, Berlin (Germany)';
$module_license = 'MIT License (MIT)';
$module_description = 'Extended WYSIWYG functions for the Content Management Systems WebsiteBaker and LEPTON CMS';
$module_home = 'http://addons.phpmanufaktur.de/extendedWYSIWYG';
$module_guid = 'D396C335-A08C-4119-8DC5-DE4DB9322C17';
