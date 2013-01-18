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

use phpManufaktur\extendedWYSIWYG\Data\View;

$View = new View();
if (false === ($section = $View->getSection($section_id)))
  trigger_error($View->getError(), E_USER_ERROR);
echo $section;
