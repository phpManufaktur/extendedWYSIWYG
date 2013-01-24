<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data\LEPTON;

use phpManufaktur\CMS\Bridge\Control\boneClass;

global $db;

class Page extends boneClass {

  public function select($page_id) {
    global $db;
    global $tools;

    try {
      $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
      $result = $db->fetchAssoc($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    $page = array();
    // loop through the result and unsanitize the returned values
    if (is_array($result)) {
      foreach ($result as $key => $value)
        $page[$key] = $tools->unsanitizeText($value);
    }
    return $page;
  } // get()

} // class mediaDirectory
