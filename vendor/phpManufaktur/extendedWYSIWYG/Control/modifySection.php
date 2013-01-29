<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\Control;

use phpManufaktur\extendedWYSIWYG\Data\editorDepartment;
use phpManufaktur\extendedWYSIWYG\Data\editorTeam;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\View;
use phpManufaktur\extendedWYSIWYG\View\viewException;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygSection;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygOptions;
use phpManufaktur\extendedWYSIWYG\Data\pageSettings;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygArchive;
use phpManufaktur\extendedWYSIWYG\Data\wysiwygTeaser;
use phpManufaktur\CMS\Bridge\Data\LEPTON as LEPTON;

class modifySection extends boneModifySection { //boneClass {

  /**
   * Action handler for class modifySection
   *
   * @return string modify dialog
   */
  public function action() {

    $controlAccess = new controlAccess(self::$PAGE_ID, self::$SECTION_ID);
    if (!$controlAccess->checkSectionAccess()) {
      $View = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
      return $View->AccessDenied();
    }

    if (isset($_REQUEST[self::REQUEST_ARCHIVE_ID.self::$SECTION_ID])) {
      // set the ARCHIVE ID
      self::$ARCHIVE_ID = (int) $_REQUEST[self::REQUEST_ARCHIVE_ID.self::$SECTION_ID];
    }
    if (isset($_REQUEST[self::REQUEST_TEASER_ID])) {
      // set the TEASER ID
      self::$TEASER_ID = (int) $_REQUEST[self::REQUEST_TEASER_ID];
    }

    // set requested action or default $command
    $action = (isset($_REQUEST[self::REQUEST_ACTION])) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_MODIFY;

    switch ($action):

    case self::ACTION_MODIFY:
    default:
      // default action, show the modify dialog
      $content = $this->actionModify();
      break;
    endswitch;

    if ($this->isError()) {
      // prompt error
      $View = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
      return $View->showError($this->getError());
    }
    return $content;
  } // action()

  /**
   * The action procedure for the modify dialog
   *
   * @return string dialog
   */
  protected function actionModify() {
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
    $View = new View\viewModifySection(self::$PAGE_ID, self::$SECTION_ID);
    return $View->dialogModify($data);
  } // actionModify()

} // class modifySection