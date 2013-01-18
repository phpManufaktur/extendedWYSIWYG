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

use phpManufaktur\CMS\Bridge\Control\boneClass;

class wysiwygSection extends boneClass {

  /**
   * Add a new, blank Section to the WYSIWYG table
   *
   * @param integer $page_id
   * @param integer $section_id
   * @return boolean
   */
  public function addBlank($page_id, $section_id) {
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
  } // addBlank()

  /**
   * Get the content for the $section_id for FRONTEND output and return it
   *
   * @param integer $section_id the section ID
   * @param boolean $edit_mode if true don't process frontend preparation
   * @return string
   */
  public function get($section_id, $edit_mode=false) {
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

    if (!$edit_mode) {
      // prepare the content for frontend output
      if ((CMS_TYPE == 'WebsiteBaker') && (CMS_VERSION == '2.8.1')) {
        // in WB 2.8.1 the [wblinks ...] must be preprocessed separate
        global $wb;
        $wb->preprocess($section);
      }
    }
    return $section;
  } // get()

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

    $text = $tools->sanitizeText(strip_tags($content));
    $content = $tools->sanitizeText($content);
    try {
      $db->update(CMS_TABLE_PREFIX.'mod_wysiwyg', array('content' => $content, 'text' => $text),
          array('section_id' => $section_id));
    } catch (\Doctrine\DBAL\DBALException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // update()

} // class getSection
