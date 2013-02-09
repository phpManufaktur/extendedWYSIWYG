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

use phpManufaktur\extendedWYSIWYG\Control\addSection;
use phpManufaktur\CMS\Bridge\Data\LEPTON\Pages;

global $admin;

$addSection = new addSection($page_id, $section_id);
if (!$addSection->exec()) {
  $admin->print_error($addSection->getError());
}

$Pages = new Pages();
// Sometimes WB forgot to set the root_parent of a new page in the correct way
if (!$Pages->fixRootParentProblem())
  $admin->print_error($Pages->getError());