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
use phpManufaktur\extendedWYSIWYG\Data\wysiwygTeaser;
use phpManufaktur\CMS\Bridge\Data\LEPTON as LEPTON;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

class viewModifySection extends boneClass {

  const ANCHOR = 'wysiwyg_';

  const REQUEST_ACTION = 'act';
  const REQUEST_TEASER_CONTENT = 'teaser_text';
  const REQUEST_TEASER_ID = 'teaser_id';

  const ACTION_MODIFY = 'mod';

  protected static $PAGE_ID = null;
  protected static $SECTION_ANCHOR = null;
  protected static $SECTION_ID = null;
  protected static $SECTION_POSITION = null;
  protected static $SECTION_IS_FIRST = null;
  protected static $TEMPLATE_PATH = null;
  protected static $ARCHIVE_ID = null;
  protected static $MODIFY_PAGE_URL = null;
  protected static $TEASER_ID = null;


  protected $lang = null;

  public function __construct($page_id, $section_id) {
    global $I18n;

    $this->setInfo('Initialize Class View\viewModifySection', __METHOD__, __LINE__);
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/';
    $this->lang = $I18n;
    self::$PAGE_ID = $page_id;
    self::$SECTION_ID = $section_id;
    self::$SECTION_ANCHOR = self::ANCHOR.self::$SECTION_ID;
    self::$MODIFY_PAGE_URL = CMS_ADMIN_URL.'/pages/modify.php';
  } // __construct()

  /**
   * Set the ARCHIVE ID
   *
   * @param integer $archive_id
   */
  public function setArchiveID($archive_id) {
    self::$ARCHIVE_ID = $archive_id;
  } // setArchiveID()

  /**
   * Get the active ARCHIVE ID
   *
   * @return integer|NULL
   */
  public function getArchiveID() {
    return self::$ARCHIVE_ID;
  } // getArchiveID()

  /**
   * Set the TEASER ID
   *
   * @param integer $teaser_id
   */
  public function setTeaserID($teaser_id) {
    self::$TEASER_ID = $teaser_id;
  } // setTeaserID()

  /**
   * Get the active TEASER ID
   *
   * @return integer|NULL
   */
  public function getTeaserID() {
    return self::$TEASER_ID;
  } // getTeaserID()

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
        'section_position' => self::$SECTION_POSITION,
        'section_is_first' => self::$SECTION_IS_FIRST,
        'CMS_ADDON_URL' => CMS_ADDON_URL,
        'anchor' => self::$SECTION_ANCHOR,
        'is_error' => (int) $this->isError(),
        'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.dwoo', $data);
  }

  /**
   * Prepare the WYSIWYG dialog, collect all data and return the filled template
   *
   * @return string WYSIWYG dialog
   */
  protected function prepareDialog() {
    global $tools;
    global $cms;

    // get the position of the section
    $section = new wysiwygSection();
    self::$SECTION_POSITION = $section->getSectionPositionInPage(self::$SECTION_ID);
    $first_section = $section->getPositionOfFirstSectionInPage(self::$PAGE_ID);
    self::$SECTION_IS_FIRST = (int) (self::$SECTION_POSITION == $first_section);

    // get the options of the section
    $opt = new wysiwygOptions();
    $options = $opt->selectArray(self::$SECTION_ID);

    // get the page settings
    $pageSettings = new pageSettings();
    $page = $pageSettings->getSettingsArray(self::$PAGE_ID);

    // get the archive content for this section
    $archive = new wysiwygArchive();
    if (!is_null(self::$ARCHIVE_ID)) {
      // select the given ARCHIVE ID
      if (false === ($archive_record = $archive->select(self::$ARCHIVE_ID))) {
        $this->setError($archive->getError(), __METHOD__, __LINE__);
        return false;
      }
    }
    else {
      // select the last record of the archive
      if (false === ($archive_record = $archive->selectLast(self::$SECTION_ID))) {
        $this->setError($archive->getError(), __METHOD__, __LINE__);
        return false;
      }
    }
    if (count($archive_record) > 0) {
      // get the content from the Archive record
      $section_content = $archive_record['content'];
      $author = $archive_record['author'];
      $publish = ($archive_record['status'] == 'ACTIVE') ? 1 : 0;
    }
    else {
      // it exists no Archive Record, we have to create one from the section
      $section_content = $section->select(self::$SECTION_ID, true);
      if ($section->isError()) {
        $this->setError($section->getError(), __METHOD__, __LINE__);
        return false;
      }
      // set the actual user as author
      $author = $cms->getUserDisplayName();
      $archive_id = -1;
      if (!$archive->insert(self::$PAGE_ID, self::$SECTION_ID, $section_content, $author, $archive_id)) {
        $this->setError($archive->getError(), __METHOD__, __LINE__);
        return false;
      }
      self::$ARCHIVE_ID = $archive_id;
      $publish = 1;
    }

    // get the list of the last archives for the selection
    if (false === ($archives = $archive->selectArchiveListForDialog(self::$SECTION_ID))) {
      $this->setError($archive->getError(), __METHOD__, __LINE__);
      return false;
    }
    // bild the <select> array
    $archive_array = array();
    foreach ($archives as $item) {
      $archive_array[$item['archive_id']] = array(
          'text' => sprintf('%s | %s', $item['timestamp'], $item['status']),
          'value' => $item['archive_id']
          );
    }

    // initialize Teaser
    $teaser = new wysiwygTeaser();

    // get the list of the last teasers for the selection
    if (false === ($teasers = $teaser->selectTeaserListForDialog(self::$PAGE_ID))) {
      $this->setError($teaser->getError(), __METHOD__, __LINE__);
      return false;
    }
    // bild the <select> array
    $teaser_archive = array();
    foreach ($teasers as $item) {
      $teaser_archive[$item['teaser_id']] = array(
          'text' => sprintf('%s | %s', $item['timestamp'], $item['status']),
          'value' => $item['teaser_id']
      );
    }

    // get the teaser content for this page
    if (!is_null(self::$TEASER_ID) && (self::$TEASER_ID > 0)) {
      // get a specific Teaser content
      if (false === ($teaser_record = $teaser->select(self::$TEASER_ID))) {
        $this->setError($teaser->getError(), __METHOD__, __LINE__);
        return false;
      }
      $teaser_text = $teaser_record['teaser_text'];
      $teaser_id = self::$TEASER_ID;
      $teaser_publish = (int) ($teaser_record['status'] == 'ACTIVE');
    }
    else {
      // get the last Teaser content
      if (false === ($teaser_record = $teaser->selectLast(self::$PAGE_ID))) {
        $this->setError($teaser->getError(), __METHOD__, __LINE__);
        return false;
      }
      // set the values from the record or default values
      $teaser_text = isset($teaser_record['teaser_text']) ? $teaser_record['teaser_text'] : '';
      $teaser_id = isset($teaser_record['teaser_id']) ? $teaser_record['teaser_id'] : -1;
      $teaser_publish = isset($teaser_record['status']) ? (int) ($teaser_record['status'] == 'ACTIVE') : 1;
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
            'text' => $section_content,
            'position' => self::$SECTION_POSITION,
            'is_first' => self::$SECTION_IS_FIRST,
            ),
        'publish' => array(
            'name' => 'publish_'.self::$SECTION_ID,
            'value' => 1,
            'status' => $publish,
            ),
        'author' => $author,
        'count' => array(
            'words' => $tools->countWords($section_content),
            'chars' => strlen(strip_tags($section_content))
            ),
        'archive' => array(
            'id' => self::$ARCHIVE_ID,
            'name' => 'archiv_id'.self::$SECTION_ID,
            'items' => $archive_array,
            'link' => sprintf('%s?%s%s&archive_id%d=',
                self::$MODIFY_PAGE_URL,
                http_build_query(array(
                    'page_id' => self::$PAGE_ID,
                    )),
                LEPTON\Leptoken::getParameterString('&'), // use LEPTOKEN if needed!
                self::$SECTION_ID
                ),
            'anchor' => self::$SECTION_ANCHOR
            ),
        'options' => array(
            'page_settings' => array(
                'active' => self::$SECTION_IS_FIRST,
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
            'use_as_blog' => array(
                'active' => self::$SECTION_IS_FIRST,
                'checkbox' => array(
                    'name' => sprintf('use_as_blog_%d', self::$SECTION_ID),
                    'value' => 1,
                    'checked' => in_array('use_as_blog', $options) ? 1 : 0
                    ),
                'fields' => array(
                    'teaser' => array(
                        'id' => $teaser_id,
                        'name' => self::REQUEST_TEASER_CONTENT,
                        'value' => $teaser_text,
                        'publish' => $teaser_publish
                        ),
                    'archive' => array(
                        'active' => empty($teaser_archive) ? 0 : 1,
                        'name' => self::REQUEST_TEASER_ID,
                        'items' => $teaser_archive,
                        'link' => sprintf('%s?%s%s&%s=',
                            self::$MODIFY_PAGE_URL,
                            http_build_query(array(
                                'page_id' => self::$PAGE_ID
                            )),
                            LEPTON\Leptoken::getParameterString('&'), // use LEPTOKEN if needed
                            self::REQUEST_TEASER_ID
                            ),
                        'anchor' => self::$SECTION_ANCHOR
                        )
                    ),
                )
            ),
        'link' => array(
            'save' => array(
                'url' => CMS_ADDON_URL.'/vendor/phpManufaktur/extendedWYSIWYG/Control/retrieveCKEditorContent.php'
                ),
            'settings' => array(
                'url' => CMS_ADDON_URL.'/service.php'
                )
            )
        );
    return $this->getTemplate('modify.dwoo', $data);
  } // view()

} // class modifySection