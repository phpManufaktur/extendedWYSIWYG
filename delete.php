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

use phpManufaktur\extendedWYSIWYG\Control\deleteSection;

global $admin;

$deleteSection = new deleteSection($page_id, $section_id);
if (!$deleteSection->exec()) {
  $admin->print_error($deleteSection->getError());
}