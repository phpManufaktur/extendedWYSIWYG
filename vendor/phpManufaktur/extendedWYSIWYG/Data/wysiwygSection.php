<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Data;

use phpManufaktur\kitCommand\kitCommand;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\kitCommand\Command\system\system;

class wysiwygSection extends boneClass {

  /**
   * Create the table mod_wysiwyg
   *
   * @return boolean
   */
  public function create() {
    global $db;

    $table = CMS_TABLE_PREFIX.'mod_wysiwyg';
$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `section_id` INT(11) NOT NULL DEFAULT '0',
      `page_id` INT(11) NOT NULL DEFAULT '0',
      `content` LONGTEXT NOT NULL,
      `text` LONGTEXT NOT NULL,
      PRIMARY KEY (`section_id`)
    )
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8
    COLLATE='utf8_unicode_ci'
EOD;
    try {
      $db->query($SQL);
      $this->setInfo('Created table mod_wysiwyg', __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // create()

  /**
   * Add a new, blank Section to the WYSIWYG table
   *
   * @param integer $page_id
   * @param integer $section_id
   * @return boolean
   */
  public function insertBlank($page_id, $section_id) {
    global $db;

    try {
      $db->insert(CMS_TABLE_PREFIX.'mod_wysiwyg', array(
          'page_id' => (int) $page_id, 'section_id' => (int) $section_id));
      $this->setInfo("Added empty WYSIWYG Section for $page_id / $section_id", __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // insertBlank()

  /**
   * Get the content for the $section_id for FRONTEND output and return it
   *
   * @param integer $section_id the section ID
   * @param boolean $edit_mode if false (default) don't process frontend preparation
   * @return string
   */
  public function select($section_id, $edit_mode=false) {
    global $db;
    global $tools;

    $section_id = (int) $section_id;
    if ($section_id < 1) {
      // invalid section id!
      $this->setError('The submitted Section ID is invalid', __METHOD__, __LINE__);
      return $this->getError();
    }
    try {
      $SQL = "SELECT content FROM `".CMS_TABLE_PREFIX."mod_wysiwyg` WHERE section_id=?";
      $section = $db->fetchAssoc($SQL, array($section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    // unsanitize the text
    $section = $tools->unsanitizeText($section['content']);

    // replace placeholders with the actual MEDIA URL
    $kitCommand = new kitCommand();
    $section = $kitCommand->Exec($section, true);

    if (!$edit_mode) {
      // prepare the content for frontend output
      if ((CMS_TYPE == 'WebsiteBaker') && (CMS_VERSION == '2.8.1')) {
        // in WB 2.8.1 the [wblinks ...] must be preprocessed separate
        global $wb;
        $wb->preprocess($section);
      }
    }

    return $section;
  } // select()

  /**
   * Delete a WYSIWYG section
   *
   * @param integer $section_id
   * @return boolean
   */
  public function delete($section_id) {
    global $db;

    try {
      $db->delete(CMS_TABLE_PREFIX.'mod_wysiwyg', array('section_id' => $section_id));
      $this->setInfo("Deleted WYSIWYG Section ".$section_id, __METHOD__, __LINE__);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // emptySection()

  /**
   * Update a WYSIWYG section
   *
   * @param integer $section_id
   * @param string $content
   * @return boolean
   */
  public function update($section_id, $content) {
    global $db;
    global $tools;
    global $cms;

    $config = new wysiwygConfiguration();
    $use_relative_url = $config->getValue('cfgUseRelativeMediaURL');
    if ($config->isError()) {
      $this->setError($config->getMessage(), __METHOD__, __LINE__);
      return false;
    }
    if ($use_relative_url) {
      // replace absolute URLs of MEDIA directory with a kitCommand
      $searchfor = '@(<[^>]*=\s*")('.preg_quote(CMS_MEDIA_URL).')([^">]*".*>)@siU';
      $content = preg_replace($searchfor, '$1~~ wysiwyg replace[CMS_MEDIA_URL] ~~$3', $content);
    }

    $text = $tools->sanitizeText(strip_tags($content));
    $content = $tools->sanitizeText($content);
    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg', array('content' => $content, 'text' => $text),
          array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    // update field "Last modified" of the page?
    $update_last_modified = $config->getValue('cfgUpdateModifiedPage');
    if ($config->isError()) {
      $this->setError($config->getMessage(), __METHOD__, __LINE__);
      return false;
    }
    if ($update_last_modified) {
      // process the page field "last modified"
      try {
        // get the PAGE ID
        $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."sections` WHERE `section_id`='$section_id'";
        $page = $db->fetchAssoc($SQL);
      } catch (\Doctrine\DBAL\DBALException $e) {
        $this->setError($e->getMessage(), __METHOD__, $e->getLine());
        return false;
      }
      try {
        // update the page fields
        $db->update(CMS_TABLE_PREFIX.'pages',
            array(
              'modified_by' => $cms->getUserID(),
              'modified_when' => time()),
            array(
                'page_id' => $page['page_id']));
      } catch (\Doctrine\DBAL\DBALException $e) {
        $this->setError($e->getMessage(), __METHOD__, $e->getLine());
        return false;
      }
    }
    return true;
  } // update()


  /**
   * Get the position of the WYSIWYG section within the page
   *
   * @param integer $section_id
   * @return number
   */
  public function getSectionPositionInPage($section_id) {
    global $db;

    try {
      $SQL = "SELECT `position` FROM `".CMS_TABLE_PREFIX."sections` WHERE `module`='wysiwyg' AND `section_id`=?";
      $position = $db->fetchAssoc($SQL, array($section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    if (!isset($position['position'])) return null;
    return (int) $position['position'];
  } // getSectionPositionWithinPage()

  /**
   * Get the position number of the first WYSIWYG section within the given page ID
   *
   * @param integer $page_id
   * @return string|NULL|number
   */
  public function getPositionOfFirstSectionInPage($page_id) {
    global $db;

    try {
      $SQL = "SELECT `position` FROM `".CMS_TABLE_PREFIX."sections` WHERE `module`='wysiwyg' AND `page_id`=? ORDER BY `position` ASC LIMIT 1";
      $position = $db->fetchAssoc($SQL, array($page_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    if (!isset($position['position'])) return null;
    return (int) $position['position'];
  } // getPositionOfFirstSectionInPage()

  /**
   * Get the SECTION IDs in order of the position within the page
   *
   * @param integer $page_id
   * @return multitype:unknown
   */
  public function getSectionIDsOrderByPosition($page_id) {
    global $db;

    try {
      $SQL = "SELECT `section_id` FROM `".CMS_TABLE_PREFIX."sections` WHERE `module`='wysiwyg' AND `page_id`='$page_id' ORDER BY `position` ASC";
      $result = $db->fetchAll($SQL);
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return $this->getError();
    }
    $sections = array();
    if (is_array($result)) {
      foreach ($result as $section)
        $sections[] = $section['section_id'];
    }
    return $sections;
  } // getSectionIDsOrderByPosition()

} // class wysiwygSection
