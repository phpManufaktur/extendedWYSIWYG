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

use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;

/**
 * This function will be called by the search function and returns the results
 * via print_excerpt2() for the specified SECTION_ID
 *
 * @param array $search
 * @return boolean
 */
function wysiwyg_search($search) {

  // we can ignore calls by DropletsExtions...
  if (isset($_SESSION['DROPLET_EXECUTED_BY_DROPLETS_EXTENSION'])) return '- passed call by DropletsExtension -';

  $section = new wysiwygSection();
  if (false === ($content = $section->select($search['section_id'])))
    trigger_error($section->getError(), E_USER_ERROR);

  if (!empty($content)) {
    // remove dbGlossary tags
    $content = str_replace('||', '', $content);
    $result = array(
        'page_link' => $search['page_link'],
        'page_link_target' => SEC_ANCHOR.$search['section_id'],
        'page_title' => $search['page_title'],
        'page_description' => $search['page_description'],
        'page_modified_when' => $search['page_modified_when'],
        'page_modified_by' => $search['page_modified_by'],
        'text' => $content.'.',
        'max_exerpt_num' => $search['default_max_excerpt']
    );
    if (print_excerpt2($result, $search))
      return true;
  }
  return false;
} // wysiwyg_search()
