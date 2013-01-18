<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\View;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\View\viewException;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

class modifySection extends boneClass {

  const ANCHOR = 'wysiwyg_';

  const REQUEST_ACTION = 'act';

  const ACTION_MODIFY = 'mod';

  protected static $PAGE_ID = null;
  protected static $SECTION_ANCHOR = null;
  protected static $SECTION_ID = null;
  protected static $TEMPLATE_PATH = null;


  protected $lang = null;

  public function __construct($page_id, $section_id) {
    global $I18n;

    $this->setInfo('Initialize Class View\modifySection', __METHOD__, __LINE__);
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/';
    $this->lang = $I18n;
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
    self::$SECTION_ANCHOR = self::ANCHOR.self::$SECTION_ID;
  } // __construct()

  /**
   * Get the template, set the data and return the compiled
   *
   * @param string $template the name of the template
   * @param array $template_data
   * @param boolean $trigger_error raise a trigger error on problems
   * @return boolean|Ambigous <string, mixed>
   */
  protected function getTemplate($template, $template_data) {
    global $dwoo;

    // check if a custom template exists ...
    $load_template = (file_exists(self::$TEMPLATE_PATH.'custom.'.$template)) ? self::TEMPLATE_PATH.'custom.'.$template : self::$TEMPLATE_PATH.$template;
    try {
      $result = $dwoo->get($load_template, $template_data);
    }
    catch (\Dwoo_Exception $e) {
      $this->setError($this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Return the completed View
   *
   * @return Ambigous <boolean, \phpManufaktur\extendedWYSIWYG\View\Ambigous, string, mixed>
   */
  public function view() {
    $content = $this->prepareDialog();

    $data = array(
        'anchor' => self::$SECTION_ANCHOR,
        'is_error' => (int) $this->isError(),
        'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.dwoo', $data);
  }

  protected function prepareDialog() {

    // get the content of the section
    $section = new wysiwygSection();
    $section_content = $section->get(self::$SECTION_ID, true);
    if ($section->isError()) {
      $this->setError($section->getError(), __METHOD__, __LINE__);
      return false;
    }


    $data = array(
        'page' => array(
            'name' => 'page_id',
            'id' => self::$PAGE_ID
            ),
        'section' => array(
            'name' => 'section_id',
            'id' => self::$SECTION_ID,
            'editor_id' => 'content_'.self::$SECTION_ID,
            'text' => $section_content
            )
        );
    return $this->getTemplate('modify.dwoo', $data);
  } // view()

} // class modifySection