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
else
  $I18n->addFile('DE.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');

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

  private static $error = '';
  private static $message = '';

  protected static $cfg_updateModifiedPage = true;
  protected static $cfg_archiveIdSelectLimit = 10;
  protected static $template_path = '';
  protected static $page_id = null;
  protected static $section_id = null;
  protected static $section_anchor = '';
  protected static $modify_url = '';
  protected static $save_url = '';
  protected static $page_tree_url = '';
  protected static $sections_url = '';

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
    // get settings
    $config = new manufakturConfig();
    self::$cfg_updateModifiedPage = $config->getValue('cfgUpdateModifiedPage', 'wysiwyg');
    self::$cfg_archiveIdSelectLimit = $config->getValue('cfgArchiveIdSelectLimit', 'wysiwyg');
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
    $data = array(
        'section_id' => self::$section_id,
        'page_id' => self::$page_id,
        'action' => array(
            'link' => self::$save_url,
            'anchor' => self::$section_anchor
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

    if (!isset($_REQUEST['content'.self::$section_id])) {
      // upps, missing content!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: Missing the WYSIWYG content for section <b>{{ section_id }}</b>!',
              array('section_id' => self::$section_id))));
      return $this->adminPrintError(self::$modify_url);
    }

    if (!isset($_REQUEST[self::REQUEST_ARCHIVE_ID])) {
      // missing the archive_id
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: Missing the ARCHIVE_ID for section <b>{{ section_id }}</b>!',
              array('section_id' => self::$section_id))));
      return $this->adminPrintError(self::$modify_url);
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
      return $this->adminPrintError(self::$modify_url);
    }
    if ($query->numRows() < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: The ARCHIVE_ID <b>{{ archive_id }}</b> does not exists!',
              array('archive_id' => $_REQUEST[self::REQUEST_ARCHIVE_ID]))));
      return $this->adminPrintError(self::$modify_url);
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
          return $this->adminPrintError(self::$modify_url);
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
        return $this->adminPrintError(self::$modify_url);
      }
      if ($publish) {
        // update the content of the WYSIWYG record
        $archive_id = mysql_insert_id();
        $SQL = sprintf("UPDATE `%smod_wysiwyg` SET `content`='%s', `text`='%s', `archive_id`='%d' WHERE `section_id`='%d'",
            TABLE_PREFIX, $content, strip_tags($content), $archive_id, self::$section_id);
        if (!$database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return $this->adminPrintError(self::$modify_url);
        }
        if (self::$cfg_updateModifiedPage) {
          // tell the PAGE record that it was updated
          $SQL = sprintf("UPDATE `%spages` SET `modified_when`='%d', `modified_by`='%d' WHERE `page_id`='%d'",
              TABLE_PREFIX, time(), $admin->get_user_id(), self::$page_id);
          if (!$database->query($SQL)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return $this->adminPrintError(self::$modify_url);
          }
        }
      }
      // all done, prompt success message
      $this->setMessage($this->lang->translate('The section <b>{{ section_id }}</b> was successfull saved.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess(self::$modify_url);
    }
    else {
      // nothing to do !!!
      $this->setMessage($this->lang->translate('The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess(self::$modify_url);
    }

// OLD FUNCTION

    // get the old md5 hash
    $old_hash = $database->get_one("SELECT `hash` FROM `".TABLE_PREFIX."mod_wysiwyg` WHERE `section_id`='".self::$section_id."'", MYSQL_ASSOC);

    if ($new_hash != $old_hash) {
      // save only if the hash has changed!
      $text = strip_tags($_REQUEST['content'.self::$section_id]);
      $SQL = sprintf("UPDATE `".TABLE_PREFIX."mod_wysiwyg` SET `content`='%s', `text`='%s', `hash`='%s' WHERE `section_id`='%d'",
          $content, $text, $new_hash, self::$section_id);
      if (!$database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return $this->adminPrintError(self::$modify_url);
      }
      if (self::$cfg_updateModifiedPage) {
        // tell the PAGE record that it was updated
        $SQL = sprintf("UPDATE `%spages` SET `modified_when`='%d', `modified_by`='%d' WHERE `page_id`='%d'",
            TABLE_PREFIX, time(), $admin->get_user_id(), self::$page_id);
        if (!$database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return $this->adminPrintError(self::$modify_url);
        }
      }
      // all done, prompt success message
      $this->setMessage($this->lang->translate('The section <b>{{ section_id }}</b> was successfull saved.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess(self::$modify_url);
    }
    else {
      // nothing to do...
      $this->setMessage($this->lang->translate('The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.',
          array('section_id' => self::$section_id)));
      return $this->adminPrintSuccess(self::$modify_url);
    }
  } // saveSection()

  protected function saveVersion() {
    global $database;

  } // saveVersion()

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

} // class extendedWYSIWYG