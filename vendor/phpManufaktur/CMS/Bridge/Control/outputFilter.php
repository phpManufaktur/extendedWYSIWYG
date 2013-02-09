<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Control;

use phpManufaktur\kitCommand\kitCommand;

$path = __DIR__;
for ($i=0; $i < 10; $i++) {
  // try to find and load the bootstrap.php
  if (@file_exists($path.'/bootstrap.php')) {
    if (!defined('EXTERNAL_ACCESS'))
      define('EXTERNAL_ACCESS', false);
    include $path.'/bootstrap.php';
    break;
  }
  $path = substr($path, 0, strrpos($path, '/'));
}

class outputFilter {

  /**
   * Execute the output filter of the cmsBridge
   *
   * @param string $content
   * @return Ambigous <string, string>
   */
  public function exec($content) {
    $load_css = '';
    $load_js = array();
    $kitCommand = new kitCommand();
    $content = $kitCommand->Exec($content, $load_css, $load_js);
    return $content;
  } // exec()

} // class outputFilter
