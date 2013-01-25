<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('EXTERNAL_ACCESS'))
  define('EXTERNAL_ACCESS', false);
include __DIR__.'/bootstrap.php';

use phpManufaktur\extendedWYSIWYG\View\viewSettings;

$settings = new viewSettings();
echo $settings->action();