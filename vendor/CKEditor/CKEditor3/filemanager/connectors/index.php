<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

$path = __DIR__;
// at maximum step 8 levels back!
for ($i=0; $i < 10; $i++) {
  $path = substr($path, 0, strrpos($path, '/'));
  // at this point we really want no error messages!
  if (@file_exists($path.'/bootstrap.php')) {
    include $path.'/bootstrap.php';
    break;
  }
}
exit('Sorry, no access!');