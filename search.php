<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH'))
  include __DIR__.'/bootstrap.php';

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

require_once LEPTON_PATH.'/modules/wysiwyg/class.wysiwyg.php';

/**
 * This function will be called by the search function and returns the results
 * via print_excerpt2() for the specified SECTION_ID
 *
 * @param array $search
 * @return boolean
 */
function wysiwyg_search($search) {
  global $database;
  $SQL = sprintf("SELECT `content` FROM `%smod_wysiwyg` WHERE `section_id`='%d'",
      TABLE_PREFIX, $search['section_id']);
  $content = $database->get_one($SQL, MYSQL_ASSOC);
  if (!empty($content)) {
    $content = extendedWYSIWYG::unsanitizeText($content);
    // remove HTML
    $content = strip_tags($content);
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
