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

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n'))
  require_once LEPTON_PATH.'/modules/'. basename(dirname(__FILE__)).'/framework/LEPTON/Helper/I18n.php';

global $I18n;
if (!is_object($I18n))
  $I18n = new LEPTON_Helper_I18n();
else
  $I18n->addFile('DE.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');

// load language depending onfiguration
if (!file_exists(LEPTON_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php'))
  require_once(LEPTON_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
else
  require_once(LEPTON_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');

if (!class_exists('Dwoo'))
  require_once LEPTON_PATH.'/modules/dwoo/include.php';

// initialize the template engine
global $parser;
if (!is_object($parser)) {
  $cache_path = LEPTON_PATH.'/temp/cache';
  if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
  $compiled_path = LEPTON_PATH.'/temp/compiled';
  if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
  $parser = new Dwoo($compiled_path, $cache_path);
}


class extendedWYSIWYG {

  protected $lang = null;

  public function __construct() {
    global $I18n;
    $this->lang = $I18n;
  } // __construct()
} // class extendedWYSIWYG