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
use phpManufaktur\extendedWYSIWYG\Data\wysiwygOptions;
use phpManufaktur\extendedWYSIWYG\Data\pageSettings;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygArchive;

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
    global $tools;
    global $cms;

    // get the content of the section
    $section = new wysiwygSection();
    $section_content = $section->select(self::$SECTION_ID, true);
    if ($section->isError()) {
      $this->setError($section->getError(), __METHOD__, __LINE__);
      return false;
    }

    // get the position of the section
    $position = $section->getSectionPositionInPage(self::$SECTION_ID);

    // get the options of the section
    $opt = new wysiwygOptions();
    $options = $opt->selectArray(self::$SECTION_ID);

    // get the page settings
    $pageSettings = new pageSettings();
    $page = $pageSettings->getSettingsArray(self::$PAGE_ID);

    // set the default author
    $author = $cms->getUserDisplayName();

    $archive = new wysiwygArchive();

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
            ),
        'publish' => $publish,
        'author' => $author,
        'count' => array(
            'words' => $tools->countWords($section_content),
            'chars' => strlen(strip_tags($section_content))
            ),
        'options' => array(
            'page_settings' => array(
                'active' => ($position == 1) ? 1 : 0,
                'checkbox' => array(
                    'name' => sprintf('page_settings_%d', self::$SECTION_ID),
                    'value' => 1,
                    'checked' => in_array('page_settings', $options) ? 1 : 0
                    ),
                'fields' => array(
                    'title' => array(
                        'name' => 'page_title',
                        'value' => $page['page_title']
                        ),
                    'description' => array(
                        'name' => 'page_description',
                        'value' => $page['description']
                        ),
                    'keywords' => array(
                        'name' => 'page_keywords',
                        'value' => $page['keywords']
                        )
                    )
                ),
            'hide_section' => array(
                'checkbox' => array(
                    'name' => sprintf('hide_section_%d', self::$SECTION_ID),
                    'value' => 1,
                    'checked' => in_array('hide_section', $options) ? 1 : 0
                    )
                ),

            ),
        'link' => array(
            'save' => array(
                'url' => CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/Control/retrieveCKEditorContent.php'
                ),
            )
        );
    return $this->getTemplate('modify.dwoo', $data);
  } // view()

} // class modifySection