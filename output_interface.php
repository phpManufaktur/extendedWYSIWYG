<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

include __DIR__.'/bootstrap.php';

use phpManufaktur\CMS\Bridge\Control\outputFilter;

if (!function_exists('wysiwyg_output_filter')) {
  function wysiwyg_output_filter($content) {
    //return outputFilter($content);
    $filter = new outputFilter();
    return $filter->exec($content);
  }
}