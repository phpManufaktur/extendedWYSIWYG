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
use phpManufaktur\extendedWYSIWYG\Data\wysiwygConfiguration;
use phpManufaktur\Toolbox\Control\Toolbox;

class archiveFile extends boneClass {


  protected static $ARCHIVE_PATH = null;
  protected static $ARCHIVE_URL = null;
  protected static $TEMPLATE_PATH = null;

  public function __construct() {
    self::$TEMPLATE_PATH = __DIR__.'/Templates/';
    self::$ARCHIVE_PATH = CMS_MEDIA_PATH.'/wysiwyg_archive';
    self::$ARCHIVE_URL = CMS_MEDIA_URL.'/wysiwyg_archive';
  } // construct()

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
    global $I18n;

    // check if a custom template exists ...
    $load_template = (file_exists(self::$TEMPLATE_PATH.'custom.'.$template)) ? self::TEMPLATE_PATH.'custom.'.$template : self::$TEMPLATE_PATH.$template;
    try {
      $result = $dwoo->get($load_template, $template_data);
    }
    catch (\Dwoo_Exception $e) {
      $this->setError($I18n->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Create the protection with .htaccess and .htpasswd for the
   * extendedWYSIWYG archive directory
   *
   * @return boolean
   */
  protected function createProtection() {
    global $tools;
    global $I18n;

    $data = array(
        'htpasswd_path' => self::$ARCHIVE_PATH.'/.htpasswd'
        );
    if (false === ($content = $this->getTemplate('htaccess.dwoo', $data))) return false;
    // write the .htaccess file
    if (false === file_put_contents(self::$ARCHIVE_PATH.'/.htaccess', $content)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $I18n->translate("Error: Can't create the <b>{{ file }}</b> file for the extendedWYSIWYG archive!",
              array('file' => '.htaccess'))));
      return false;
    }

    $data = array(
        'password' => crypt($tools->generatePassword())
        );
    if (false === ($content = $this->getTemplate('htpasswd.dwoo', $data))) return false;
    // write the .htaccess file
    if (false === file_put_contents(self::$ARCHIVE_PATH.'/.htpasswd', $content)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $I18n->translate("Error: Can't create the <b>{{ file }}</b> file for the extendedWYSIWYG archive!",
              array('file' => '.htpasswd'))));
      return false;
    }
    return true;
  } // createProtection()


  /**
   * Save the content of the archive with the desired ID as HTML file in the
   * configured archive path
   *
   * @param integer $archive_id
   * @param string &$archive_file the create archive filename
   * @return boolean
   */
  public function save($archive_id, &$archive_file='') {
    global $tools;
    global $I18n;
    global $cms;

    // check if the protection exists
    if (!file_exists(self::$ARCHIVE_PATH.'/.htaccess')) {
      if (!$this->createProtection()) return false;
    }

    // get the archive content
    $wysiwygArchive = new wysiwygArchive();
    if (false === ($archive = $wysiwygArchive->select($archive_id))) {
      $this->setError($wysiwygArchive->getError(), __METHOD__, __LINE__);
      return false;
    }
    if (count($archive) < 1) {
      $this->setError($I18n->translate('The archive with the ID {{ archive_id }} does not exists!',
          array('archive_id' => $archive_id)), __METHOD__, __LINE__);
      return false;
    }
    $archive_file = sprintf('%s-%05d-%05d-%05d.html',
        date('Ymd-His', strtotime($archive['timestamp'])),
        $archive['page_id'],
        $archive['section_id'],
        $archive_id
        );

    $target_path = self::$ARCHIVE_PATH.DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.$archive['page_id'];

    // check the path to the archive directory
    if (!$tools->checkDirectory($target_path)) {
      $this->setError($tools->getError(), __METHOD__, __LINE__);
      return false;
    }

    // create the archive file
    $data = array(
        'title' => sprintf('extendedWYSIWYG Archive File - %s', date('Y-m-d H:i:s', strtotime($archive['timestamp']))),
        'content' => $archive['content']
    );
    $html = $this->getTemplate('archiveFile.dwoo', $data);
    if (!file_put_contents($target_path.DIRECTORY_SEPARATOR.$archive_file, $html)) {
      $this->setError($I18n->translate("Can't create the file <b>{{ file }}</b>!",
              array('file' => $target_path.DIRECTORY_SEPARATOR.$archive_file)));
      return false;
    }
    return true;
  } // save()

} // class archiveFile