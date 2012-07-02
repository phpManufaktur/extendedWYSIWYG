<?php

/**
 * extendedWYSIWYG
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

// manufakturConfig
require_once LEPTON_PATH.'/modules/manufaktur_config/class.dialog.php';

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n'))
  require_once LEPTON_PATH.'/modules/manufaktur_config/framework/LEPTON/Helper/I18n.php';

global $I18n;
if (!is_object($I18n))
  $I18n = new LEPTON_Helper_I18n();

if (file_exists(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/'.LANGUAGE.'.php')) {
  $I18n->addFile(LANGUAGE.'.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

if (!class_exists('Dwoo'))
  require_once LEPTON_PATH.'/modules/dwoo/include.php';

// initialize the template engine
global $parser;
if (!is_object($parser)) {
  $cache_path = LEPTON_PATH.'/temp/cache';
  if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
  $compiled_path = LEPTON_PATH.'/temp/compiled';
  if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
  $parser = new Dwoo($compiled_path, $cache_path);
}
// load extensions for the template engine
$loader = $parser->getLoader();
$loader->addDirectory(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/plugins/');


global $id_list;
global $preview;

class extendedWYSIWYG {

  const REQUEST_ACTION = 'act';
  const REQUEST_CHANGE_SECTION = 'sec';
  const REQUEST_PAGE_ID = 'page_id';
  const REQUEST_SECTION_ID = 'section_id';
  const REQUEST_PUBLISH = 'publish';
  const REQUEST_ARCHIVE_ID = 'archive_id';
  const REQUEST_REMARK = 'remark';

  const ACTION_ABOUT = 'abt';
  const ACTION_DEFAULT = 'def';
  const ACTION_MODIFY = 'mod';
  const ACTION_SAVE = 'save';
  const ACTION_CONFIG = 'cfg';
  const ACTION_VIEW = 'view';
  const ACTION_DELETE = 'del';

  const ANCHOR = 'wysiwyg_';
  const PROTECTION_FOLDER = 'wysiwyg_archive';

  private static $error = '';
  private static $message = '';

  protected static $cfg_updateModifiedPage = true;
  protected static $cfg_archiveIdSelectLimit = 10;
  protected static $cfg_createArchiveFiles = false;
  protected static $template_path = '';
  protected static $page_id = null;
  protected static $section_id = null;
  protected static $section_anchor = '';
  protected static $modify_url = '';
  protected static $save_url = '';
  protected static $page_tree_url = '';
  protected static $sections_url = '';
  protected static $protection_path = '';
  protected static $protection_url = '';

  protected $lang = null;

  /**
   * Constructor for class extendedWYSIWYG
   */
  public function __construct($section_id, $page_id) {
    global $I18n;
    $this->lang = $I18n;
    self::$template_path = LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/backend/';
    self::$page_id = $page_id;
    self::$section_id = $section_id;
    self::$section_anchor = self::ANCHOR.self::$section_id;
    self::$modify_url = ADMIN_URL.'/pages/modify.php';
    self::$save_url = ADMIN_URL.'/pages/save.php';
    self::$page_tree_url = ADMIN_URL.'/pages/index.php';
    self::$sections_url = ADMIN_URL.'/pages/sections.php';
    self::$protection_path = LEPTON_PATH.MEDIA_DIRECTORY.'/'.self::PROTECTION_FOLDER;
    self::$protection_url = LEPTON_URL.MEDIA_DIRECTORY.'/'.self::PROTECTION_FOLDER;
    // get settings
    $config = new manufakturConfig();
    self::$cfg_updateModifiedPage = $config->getValue('cfgUpdateModifiedPage', 'wysiwyg');
    self::$cfg_archiveIdSelectLimit = $config->getValue('cfgArchiveIdSelectLimit', 'wysiwyg');
    self::$cfg_createArchiveFiles = $config->getValue('cfgCreateArchiveFiles', 'wysiwyg');
  } // __construct()

  /**
   * Set self::$error to $error
   *
   * @param string $error
   */
  protected function setError($error) {
    self::$error = $error;
  } // setError()

  /**
   * Get Error from self::$error;
   *
   * @return string $this->error
   */
  public function getError() {
    return self::$error;
  } // getError()


  /**
   * Check if self::$error is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty(self::$error);
  } // isError


  /**
   * Set self::$message to $message
   *
   * @param string $message
   */
  protected function setMessage($message) {
    self::$message = $message;
  } // setMessage()


  /**
   * Get Message from self::$message;
   *
   * @return string self::$message
   */
  public function getMessage() {
    return self::$message;
  } // getMessage()


  /**
   * Check if self::$message is empty
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) !empty(self::$message);
  } // isMessage


  /**
   * Return the version of the module
   *
   * @return float
   */
  public static function getVersion() {
    // read info.php into array
    $info_text = file(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    return -1;
  } // getVersion()

  /**
   * Get the template, set the data and return the compiled result
   *
   * @param string $template the name of the template
   * @param array $template_data
   * @param boolean $trigger_error raise a trigger error on problems
   * @return boolean|Ambigous <string, mixed>
   */
  protected function getTemplate($template, $template_data, $trigger_error=false) {
    global $parser;
    // check if a custom template exists ...
    $load_template = (file_exists(self::$template_path.'custom.'.$template)) ? self::$template_path.'custom.'.$template : self::$template_path.$template;
    try {
      $result = $parser->get($load_template, $template_data);
    }
    catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
              array(
                  'template' => basename($load_template),
                  'error' => $e->getMessage())
              )
          ));
      if ($trigger_error)
        trigger_error($this->getError(), E_USER_ERROR);
      return false;
    }
    return $result;
  } // getTemplate()


  /**
   * Prevent XSS Cross Site Scripting
   *
   * @param reference array $request
   * @return $request
   */
  protected function xssPrevent(&$request) {
    if (is_string($request)) {
      $request = html_entity_decode($request);
      $request = strip_tags($request);
      $request = trim($request);
      $request = stripslashes($request);
    }
    return $request;
  } // xssPrevent()

  /**
   * The action handler of extendedWYSIWYG
   *
   * @return string dialog or error message
   */
  public function action($command=self::ACTION_DEFAULT) {
    // placeholder for fields which are allowed to contain HTML code
    $html_allowed = array();
    foreach ($_REQUEST as $key => $value) {
      // ignore config values!
      if (strpos($key, 'CFG_') == 0) continue;
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }
    // set requested action or default $command
    $action = (isset($_REQUEST[self::REQUEST_ACTION]) && isset($_REQUEST[self::REQUEST_CHANGE_SECTION]) &&
        ($_REQUEST[self::REQUEST_CHANGE_SECTION] == self::$section_id)) ? $_REQUEST[self::REQUEST_ACTION] : $command;
    switch ($action) {
      case self::ACTION_DELETE:
        $result = $this->deleteSection();
        break;
      case self::ACTION_VIEW:
        // return the content only
        $result = $this->viewSection();
        break;
      case self::ACTION_SAVE:
        // special case: saveSection() will redirect to extendedWYSIWYG via $admin
        $result = $this->saveSection();
        break;
      case self::ACTION_CONFIG:
        $result = $this->show(self::ACTION_CONFIG, $this->dlgConfig());
        break;
      case self::ACTION_ABOUT:
        $result = $this->show(self::ACTION_ABOUT, $this->dlgAbout());
        break;
      default:
        $result = $this->show(self::ACTION_MODIFY, $this->dlgModify());
        break;
    } // switch
    // prompt the result
    echo $result;
  } // action()

  /**
   * Return the content formatted in the body container
   *
   * @param string $action
   * @param string $content
   */
  protected function show($action, $content) {
    $data = array(
        'anchor' => self::$section_anchor,
        'navigation' => '',
        'is_error' => $this->isError() ? 1 : 0,
        'content' => $this->isError() ? $this->getError() : $content
        );
    return $this->getTemplate('body.lte', $data, true);
  } // show()

  /**
   * Return the complete WYSIWYG modify dialog
   *
   * @return boolean|Ambigous <boolean, Ambigous, string, mixed>
   */
  protected function dlgModify() {
    global $database;
    global $id_list;
    global $preview;
    global $content;
    global $admin;

    if (isset($preview) && $preview == true) {
      // this is a very special solution to keep the WYSIWYG-Admin alive...
      $SQL = sprintf("SELECT `content` FROM `%smod_wysiwyg` WHERE `section_id`='%d'",
          TABLE_PREFIX, self::$section_id);
      if (false === ($content = $database->get_one($SQL))) {
        trigger_error(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()), E_USER_ERROR);
      }
      require_once(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
      return false;
    }

    if (isset($_REQUEST[self::REQUEST_ARCHIVE_ID.self::$section_id])) {
      // get the choosen ARCHIVE_ID
      $SQL = sprintf("SELECT * FROM `%smod_wysiwyg_archive` WHERE `archive_id`='%d'",
          TABLE_PREFIX, $_REQUEST[self::REQUEST_ARCHIVE_ID.self::$section_id]);
    }
    else {
      // get the last edit section
      $SQL = sprintf("SELECT * FROM `%smod_wysiwyg_archive` WHERE `section_id`='%d' ORDER BY `archive_id` DESC LIMIT 1",
          TABLE_PREFIX, self::$section_id);
    }
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() > 0) {
      // get the data from the archive record
      $section = $query->fetchRow(MYSQL_ASSOC);
      $author = $section['author'];
      $archive_id = $section['archive_id'];
      $content = self::unsanitizeText($section['content']);
      $publish = ($section['status'] == 'ACTIVE') ? 1 : 0;
    }
    else {
      // no archive entry! Create a new archive record from WYSIWYG.
      $SQL = sprintf("SELECT `content` FROM `%smod_wysiwyg` WHERE `section_id`='%d'",
          TABLE_PREFIX, self::$section_id);
      if (false === ($content = $database->get_one($SQL))) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      $author = $admin->get_display_name();
      $SQL = sprintf("INSERT INTO `%smod_wysiwyg_archive` (`section_id`,`page_id`,`content`,`hash`,`remark`,`author`,`status`)".
          " VALUES ('%d','%d','%s','%s','SYSTEM','%s','ACTIVE')",
          TABLE_PREFIX, self::$section_id, self::$page_id, $content, md5($content), $author);
      if (!$database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      $archive_id = mysql_insert_id();
      $content = self::unsanitizeText($content);
      $publish = (int) true;
    }

    $SQL = sprintf("SELECT `timestamp`, `status`, `archive_id` FROM `%smod_wysiwyg_archive` WHERE `section_id`='%d' ORDER BY `archive_id` DESC LIMIT %d",
        TABLE_PREFIX, self::$section_id, self::$cfg_archiveIdSelectLimit);
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $archive = array();
    while (false !== ($entry = $query->fetchRow(MYSQL_ASSOC))) {
      $archive[$entry['archive_id']] = array(
          'text' => sprintf('%s | %s', $entry['timestamp'], $entry['status']),
          'value' => $entry['archive_id']
          );
    }

    $leptoken = (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&leptoken=%s', $_GET['leptoken']) : '';
    if (method_exists($admin, 'getFTAN')) {
      $ftan = $admin->getFTAN(false);
      list($ftan_name, $ftan_value) = explode('=', $ftan);
    }
    else {
      $ftan_name = 'ftan_name';
      $ftan_value = 'ftan_value';
    }
    $data = array(
        'section_id' => self::$section_id,
        'page_id' => self::$page_id,
        'action' => array(
            'link' => self::$save_url,
            'anchor' => self::$section_anchor
            ),
        'ftan' => array(
            'active' => method_exists($admin, 'getFTAN') ? 1 : 0,
            'name' => $ftan_name,
            'value' => $ftan_value
            ),
        'content' => array(
            'content' => $content,
            'id' => 'content'.self::$section_id,
            ),
        'archive' => array(
            'id' => $archive_id,
            'name' => self::REQUEST_ARCHIVE_ID.self::$section_id,
            'items' => $archive,
            'link' => sprintf('%s?%s%s&archive_id%d=',
                self::$modify_url,
                http_build_query(array(
                    self::REQUEST_PAGE_ID => self::$page_id,
                    )),
                $leptoken,
                self::$section_id),
            'anchor' => self::$section_anchor),
        'publish' => $publish,
        'author' => $author,
        'config' => array(
            'link' => sprintf('%s?%s#%s',
                self::$modify_url,
                http_build_query(array(
                    self::REQUEST_PAGE_ID => self::$page_id,
                    self::REQUEST_ACTION => self::ACTION_CONFIG,
                    self::REQUEST_CHANGE_SECTION => self::$section_id
                    )),
                self::$section_anchor)
            ),
        'about' => array(
            'link' => sprintf('%s?%s#%s',
                self::$modify_url,
                http_build_query(array(
                    self::REQUEST_PAGE_ID => self::$page_id,
                    self::REQUEST_ACTION => self::ACTION_ABOUT,
                    self::REQUEST_CHANGE_SECTION => self::$section_id
                )),
                self::$section_anchor)
        )
        );
    return $this->getTemplate('modify.lte', $data);
  } // dlgModify()

  /**
   * Print a success message with the $admin function and redirect to /pages/modify.php
   * with the actual PAGE_ID and jump to the active section.
   *
   * @param string $message
   */
  protected function adminPrintSuccess($target_url) {
    global $admin;
    $admin->print_success($this->getMessage(), sprintf('%s?%s#%s',
        $target_url,
        http_build_query(array(
            self::REQUEST_PAGE_ID => self::$page_id)),
        self::$section_anchor));
  } // adminPrintSuccess()

  /**
   * Print an error with the $admin function and redirect to /pages/modify.php
   * with the actual PAGE_ID and jump to the active section.
   */
  protected function adminPrintError($target_url) {
    global $admin;
    $admin->print_error($this->getError(), sprintf('%s?%s#%s',
        $target_url,
        http_build_query(array(
            self::REQUEST_PAGE_ID => self::$page_id)),
        self::$section_anchor));
  } // adminPrintError()

  /**
   * Sanitize variables and prepare them for saving in a MySQL record
   *
   * @param mixed $item
   * @return mixed
   */
  public static function sanitizeVariable($item) {
    if (!is_array($item)) {
      // undoing 'magic_quotes_gpc = On' directive
      if (get_magic_quotes_gpc())
        $item = stripcslashes($item);
      $item = self::sanitizeText($item);
    }
    return $item;
  } // sanitizeVariable()

  /**
   * Sanitize a text variable and prepare ist for saving in a MySQL record
   *
   * @param string $text
   * @return string
   */
  protected static function sanitizeText($text) {
    $text = str_replace(array("<",">","\"","'"), array("&lt;","&gt;","&quot;","&#039;"), $text);
    $text = mysql_real_escape_string($text);
    return $text;
  } // sanitizeText()

  /**
   * Unsanitize a text variable and prepare it for output
   *
   * @param string $text
   * @return string
   */
  public static function unsanitizeText($text) {
    $text = stripcslashes($text);
    $text = str_replace(array("&lt;","&gt;","&quot;","&#039;"), array("<",">","\"","'"), $text);
    return $text;
  } // unsanitizeText()

  /**
   * Save the WYSIWYG for the desired SECTION_ID and return to admin's
   * page/modify.php
   *
   * @return mixed admin's dialog
   */
  protected function saveSection() {
    global $database;
    global $admin;

    $modify_url = sprintf('%s?page_id=%d%s',
        self::$modify_url,
        self::$page_id,
        (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&leptoken=%s', $_GET['leptoken']) : '');
    if (!isset($_REQUEST['content'.self::$section_id])) {
      // upps, missing content!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: Missing the WYSIWYG content for section <b>{{ section_id }}</b>!',
              array('section_id' => self::$section_id))));
      return $this->adminPrintError($modify_url);
    }

    if (!isset($_REQUEST[self::REQUEST_ARCHIVE_ID])) {
      // missing the archive_id
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: Missing the ARCHIVE_ID for section <b>{{ section_id }}</b>!',
              array('section_id' => self::$section_id))));
      return $this->adminPrintError($modify_url);
    }

    // get the content and sanitize it
    $content = self::sanitizeVariable($_REQUEST['content'.self::$section_id]);
    // get the md5 hash of the content
    $new_hash = md5($content);

    // get the archive record for compare
    $SQL = sprintf("SELECT * FROM `%smod_wysiwyg_archive` WHERE `archive_id`='%d'",
        TABLE_PREFIX, $_REQUEST[self::REQUEST_ARCHIVE_ID]);
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return $this->adminPrintError($modify_url);
    }
    if ($query->numRows() < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: The ARCHIVE_ID <b>{{ archive_id }}</b> does not exists!',
              array('archive_id' => $_REQUEST[self::REQUEST_ARCHIVE_ID]))));
      return $this->adminPrintError($modify_url);
    }
    $old_archive = $query->fetchRow(MYSQL_ASSOC);

    $publish = isset($_REQUEST[self::REQUEST_PUBLISH]) ? true : false;
    if (($new_hash != $old_archive['hash']) || ($publish && ($old_archive['status'] != 'ACTIVE'))) {
      // the content has changed!
      if ($publish) {
        // set the ACTIVE content from ARCHIVE to BACKUP
        $SQL = sprintf("UPDATE `%smod_wysiwyg_archive` SET `status`='BACKUP' WHERE `status`='ACTIVE' AND `section_id`='%d'",
            TABLE_PREFIX, self::$section_id);
        if (!$database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return $this->adminPrintError($modify_url);
        }
      }
      // insert a new record to ARCHIVE
      $SQL = sprintf("INSERT INTO `%smod_wysiwyg_archive` (`section_id`,`page_id`,`content`,`hash`,`remark`,`author`,`status`)".
          " VALUES ('%d','%d','%s','%s','%s','%s','%s')",
          TABLE_PREFIX, self::$section_id, self::$page_id, $content, $new_hash,
          (isset($_REQUEST[self::REQUEST_REMARK])) ? $_REQUEST[self::REQUEST_REMARK] : '',
          $admin->get_display_name(), ($publish) ? 'ACTIVE' : 'UNPUBLISHED');
      if (!$database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return $this->adminPrintError($modify_url);
      }
      if ($publish) {
        // update the content of the WYSIWYG record
        $archive_id = mysql_insert_id();
        $SQL = sprintf("UPDATE `%smod_wysiwyg` SET `content`='%s', `text`='%s' WHERE `section_id`='%d'",
            TABLE_PREFIX, $content, strip_tags($content), self::$section_id);
        if (!$database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return $this->adminPrintError($modify_url);
        }
        if (self::$cfg_updateModifiedPage) {
          // tell the PAGE record that it was updated
          $SQL = sprintf("UPDATE `%spages` SET `modified_when`='%d', `modified_by`='%d' WHERE `page_id`='%d'",
              TABLE_PREFIX, time(), $admin->get_user_id(), self::$page_id);
          if (!$database->query($SQL)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return $this->adminPrintError($modify_url);
          }
        }
      }
      // create an extra archive file?
      if (self::$cfg_createArchiveFiles && !$this->createArchiveFile($_REQUEST[self::REQUEST_ARCHIVE_ID])) {
        return $this->adminPrintError($modify_url);
      }
      // all done, prompt success message
      $this->setMessage($this->lang->translate('The section <b>{{ section_id }}</b> was successfull saved.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess($modify_url);
    }
    else {
      // nothing to do !!!
      $this->setMessage($this->lang->translate('The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess($modify_url);
    }
  } // saveSection()

  /**
   * Create a archive file in a protected /MEDIA directory and copy the embedded
   * files in this directory
   *
   * @param integer $archive_id
   * @return boolean
   */
  protected function createArchiveFile($archive_id) {
    global $database;
    // check directory
    if (!file_exists(self::$protection_path)) {
      if (!mkdir(self::$protection_path, 0755)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate("Error: Can't create the directory <b>{{ directory }}</b>!",
                array('directory', self::$protection_path))));
        return false;
      }
    }
    // check protection
    if (!file_exists(self::$protection_path.'.htaccess')) {
      if (!$this->createProtection()) return false;
    }
    // get the record for $archive_id
    $SQL = sprintf("SELECT * FROM `%smod_wysiwyg_archive` WHERE `archive_id`='%d'",
        TABLE_PREFIX, $archive_id);
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $archive = $query->fetchRow(MYSQL_ASSOC);

    // get the page link
    $SQL = sprintf("SELECT `link` FROM `%spages` WHERE `page_id`='%d'", TABLE_PREFIX, $archive['page_id']);
    $page_link = $database->get_one($SQL, MYSQL_ASSOC);

    $time = strtotime($archive['timestamp']);
    $directory_path = sprintf('%s%s/%s', self::$protection_path, $page_link, date('ymd-His'));
    $directory_url = sprintf('%s%s/%s', self::$protection_url, $page_link, date('ymd-His'));
    if (!file_exists($directory_path)) {
      if (!mkdir($directory_path, 0755, true)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate("Error: Can't create the directory <b>{{ directory }}</b>!",
                array('directory', $directory_path))));
        return false;
      }
    }
    $content = self::unsanitizeText($archive['content']);
    preg_match_all('/src="[^"]+"/', $content, $matches);
    foreach ($matches as $match) {
      foreach ($match as $img) {
        $old_url = substr($img, strlen('src="'), strlen($img)-(strlen('src="')+1));
        if (strpos($old_url, LEPTON_URL.MEDIA_DIRECTORY) !== false) {
          $dir = substr($old_url, strlen(LEPTON_URL.MEDIA_DIRECTORY));
          $old_path = LEPTON_PATH.MEDIA_DIRECTORY.$dir;
          $new_path = $directory_path.$dir;
          $new_url = $directory_url.$dir;
          if (!file_exists(dirname($new_path))) {
            // create subdirectory
            if (!mkdir(dirname($new_path), 0755, true)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                  $this->lang->translate("Error: Can't create the directory <b>{{ directory }}</b>!",
                      array('directory', dirname($new_path)))));
              return false;
            }
          }
          // copy file from old source to the archive directory
          if (!copy($old_path, $new_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                $this->lang->translate("Error: Can't copy the file <b>{{ source }}</b> to <b>{{ destination }}</b>!",
                    array('source' => $old_path, 'destination' => $new_path))));
            return false;
          }
          // update the content
          $content = str_replace($old_url, $new_url, $content);
        } // matches /MEDIA directory
      }
      // create the archive file
      $data = array(
          'title' => sprintf('extendedWYSIWYG Archive File - %s', date('Y-m-d H:i:s', $time)),
          'content' => $content
      );
      $html = $this->getTemplate('archive_file.lte', $data);
      if (!file_put_contents($directory_path.'/index.html', $html)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate("Error: Can't write the file <b>{{ file }}</b>!",
                array('file' => $directory_path.'/index.html'))));
        return false;
      }
      return true;
    }
  } // createArchiveFile()

  /**
   * Generate a random password of $length
   *
   * @param integer $length
   * @return string password
   */
  protected static function generatePassword($length=12) {
    $new_pass = '';
    $salt = 'abcdefghjkmnpqrstuvwxyz123456789';
    srand((double) microtime() * 1000000);
    for ($i=0; $i < $length; $i++) {
      $num = rand() % 33;
      $tmp = substr($salt, $num, 1);
      $new_pass = $new_pass . $tmp;
    }
    return $new_pass;
  } // generatePassword()

  /**
   * Create a .htacces and a .htpasswd file for the protected directory of
   * extendedWYSIWYG for archived files
   *
   * @return boolean
   */
  protected function createProtection() {
    $data = sprintf("# .htaccess generated by extendedWYSIWYG\nAuthUserFile %s\nAuthGroupFile /dev/null"."\nAuthName \"extendedWYSIWYG - Protected Media Directory\"\nAuthType Basic\n<Limit GET>\n"."require valid-user\n</Limit>",
        self::$protection_path.'.htpasswd');
    if (false === file_put_contents(self::$protection_path.'.htaccess', $data)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate("Error: Can't create the <b>{{ file }}</b> file for the protected WYSIWYG folder!", array('file' => '.htaccess'))));
      return false;
    }
    $data = sprintf("# .htpasswd generated by extendedWYSIWYG\nwysiwyg_protector:%s", crypt(self::generatePassword()));
    if (false === file_put_contents(self::$protection_path.'.htpasswd', $data)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate("Error: Can't create the <b>{{ file }}</b> file for the protected WYSIWYG folder!", array('file' => '.htpasswd'))));
      return false;
    }
    return true;
  } // createProtection()


  /**
   * Select the HTML content for the SECTION_ID and return it
   *
   * @param $section_id integer SECTION_ID
   * @return string content or error message
   */
  public static function viewSection($section_id) {
    global $database;
    // select content for SECTION_ID from record
    $SQL = sprintf("SELECT `content` FROM `%smod_wysiwyg` WHERE `section_id`='%d'",
        TABLE_PREFIX, $section_id);
    if (null === ($content = $database->get_one($SQL, MYSQL_ASSOC))) {
      return sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error());
    }
    if (!defined('LEPTON_VERSION') && (WB_VERSION == '2.8.1')) {
      global $wb;
      $content = self::unsanitizeText($content);
      // WB 2.8.1 needs an additional preprocess to replace the [wblinks] with real links
      $wb->preprocess($content);
      return $content;
    }
    // important: unsanitize!
    return self::unsanitizeText($content);
  } // viewSection()

  /**
   * Delete the WYSIWYG for the desired SECTION_ID
   * This function must return an empty string because the admin functions will
   * include the class, receive the result and process ahead.
   *
   * @return string (empty string)
   */
  protected function deleteSection() {
    global $database;
    // suppress LEPTON automatic error prompt
    if (defined('LEPTON_VERSION'))
      $database->prompt_on_error(false);
    // Delete record from the database
    $SQL = sprintf("DELETE FROM `%smod_wysiwyg` WHERE `section_id`='%d'",
        TABLE_PREFIX, self::$section_id);
    if (null === ($database->query($SQL))) {
      trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);
      return '';
    }
    return '';
  } // deleteSection()

  /**
   * Call the manufakturConfig Dialog for the settings
   *
   * @return Ambigous <string, Ambigous, boolean, mixed>
   */
  protected function dlgConfig() {
    // set the link to call the dlgConfig()
    $link = sprintf('%s?%s#%s',
        self::$modify_url,
        http_build_query(array(
            self::REQUEST_ACTION => self::ACTION_CONFIG,
            self::REQUEST_CHANGE_SECTION => self::$section_id,
            self::REQUEST_PAGE_ID => self::$page_id)),
        self::$section_anchor);
    // set the abort link (to modify page)
    $abort = sprintf('%s?%s#%s',
        self::$modify_url,
        http_build_query(array(
            self::REQUEST_PAGE_ID => self::$page_id
            )),
        self::$section_anchor);
    // exec manufakturConfig
    $dialog = new manufakturConfigDialog('wysiwyg', 'extendedWYSIWYG', $link, $abort);
    return $dialog->action();
  } // dlgConfig()

  protected function dlgAbout() {
    $notes = file_get_contents(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/CHANGELOG');
    $use_markdown = 0;
    if (file_exists(LEPTON_PATH.'/modules/lib_markdown/standard/markdown.php')) {
      require_once LEPTON_PATH.'/modules/lib_markdown/standard/markdown.php';
      $notes = Markdown($notes);
      $use_markdown = 1;
    }
    $data = array(
        'logo_src' => LEPTON_URL.'/modules/wysiwyg/images/extendedwysiwyg_250x167.jpg',
        'release' => array(
            'number' => $this->getVersion(),
            'notes' => $notes,
            'use_markdown' => $use_markdown
            ),
        'abort_location' => sprintf('%s?%s#%s',
            self::$modify_url,
            http_build_query(array(
                self::REQUEST_PAGE_ID => self::$page_id)),
            self::$section_anchor)
        );
    return $this->getTemplate('about.lte', $data);
  } // dlgAbout()

} // class extendedWYSIWYG