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

use phpManufaktur\extendedWYSIWYG\Control\modifySection;

$modify = new modifySection($page_id, $section_id);
echo $modify->action();
