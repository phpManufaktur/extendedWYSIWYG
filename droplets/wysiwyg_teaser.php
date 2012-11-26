<?php

/**
 * extendedWYSIWYG
 *
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 by phpManufaktur
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n'))
  require_once WB_PATH.'/modules/manufaktur_config/framework/LEPTON/Helper/I18n.php';

global $I18n;
if (!is_object($I18n))
  $I18n = new LEPTON_Helper_I18n();

if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/'.LANGUAGE.'.php')) {
  $I18n->addFile(LANGUAGE.'.php', WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

global $database;

$param_order = (isset($order) && (strtolower($order) == 'asc')) ? 'ASC' : 'DESC';
$param_limit = (isset($limit)) ? (int) $limit : 5;
$param_title = (isset($title) && (strtolower($title) == 'false')) ? false : true;
$param_css = (isset($css) && (strtolower($css) == 'false')) ? false : true;
$param_link = (isset($link) && (strtolower($link) == 'false')) ? false : true;

// exists dropletsExtension?
if (file_exists(WB_PATH.'/modules/droplets_extension/interface.php')) {
  // load dropletsExtension
  require_once(WB_PATH.'/modules/droplets_extension/interface.php');
  if ($param_css) {
    // load CSS!
    if (!is_registered_droplet_css('wysiwyg_teaser', PAGE_ID)) {
      register_droplet_css('wysiwyg_teaser', PAGE_ID, 'wysiwyg', 'wysiwyg_teaser.css');
    }
  }
  elseif (is_registered_droplet_css('wysiwyg_teaser', PAGE_ID)) {
    unregister_droplet_css('wysiwyg_teaser', PAGE_ID);
  }
}
$result = '';

$SQL = "SELECT * FROM `".TABLE_PREFIX."mod_wysiwyg_teaser` WHERE `status`='ACTIVE' ORDER BY `date_publish` $param_order LIMIT $param_limit";
$query = $database->query($SQL);
if ($database->is_error())
  return $database->get_error();
if ($query->numRows() < 1) {
  // no active teaser
  $result = $I18n->translate('<p>- no active teaser -</p>');
}
else {
  // build the teasers
  while (false !== ($teaser = $query->fetchRow(MYSQL_ASSOC))) {
    // start teaser item container
    $result .= '<div class="wysiwyg_teaser_item">';
    // set title?
    if ($param_title) {
      $title = $database->get_one("SELECT `page_title` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='{$teaser['page_id']}'", MYSQL_ASSOC);
      $result .= sprintf('<div class="wysiwyg_teaser_title">%s</div>', $title);
    }
    // add the teaser content
    $content = stripcslashes($teaser['teaser_text']);
    $content = str_replace(array("&lt;","&gt;","&quot;","&#039;"), array("<",">","\"","'"), $content);
    $result .= sprintf('<div class="wysiwyg_teaser_item_content">%s</div>', $content);
    // add the link to the article
    if ($param_link) {
      $link = $database->get_one("SELECT `link` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='{$teaser['page_id']}'", MYSQL_ASSOC);
      $link = WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
      $result .= sprintf('<div class="wysiwyg_teaser_link"><a href="%s">%s</a></div>', $link, $I18n->translate('read more ...'));
    }
    // end teaser item container
    $result .= '</div>';
  }
}

// return the container
return sprintf('<div id="wysiwyg_teaser">%s</div>', $result);
