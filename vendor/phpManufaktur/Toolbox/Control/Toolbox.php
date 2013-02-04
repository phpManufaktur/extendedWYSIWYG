<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Toolbox\Control;

use phpManufaktur\CMS\Bridge\Control\boneClass;

class Toolbox extends boneClass {

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
  public static function sanitizeText($text) {
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
   * Count words as proposed from stano110@azet.sk at
   * http://de2.php.net/manual/de/function.str-word-count.php
   * and works well with UTF-8
   *
   * @param string $string
   * @return number
   */
  public static function countWords($string)  {
    $string = htmlspecialchars_decode(strip_tags($string));
    if (strlen($string)==0)
      return 0;
    // separators
    $t = array(' '=>1, '_'=>1, "\x20"=>1, "\xA0"=>1, "\x0A"=>1, "\x0D"=>1, "\x09"=>1,
        "\x0B"=>1, "\x2E"=>1, "\t"=>1, '='=>1, '+'=>1, '-'=>1, '*'=>1, '/'=>1, '\\'=>1,
        ','=>1, '.'=>1, ';'=>1, ':'=>1, '"'=>1, '\''=>1, '['=>1, ']'=>1, '{'=>1, '}'=>1,
        '('=>1, ')'=>1, '<'=>1, '>'=>1, '&'=>1, '%'=>1, '$'=>1, '@'=>1, '#'=>1, '^'=>1,
        '!'=>1, '?'=>1);
    $count= isset($t[$string[0]]) ? 0:1;
    if (strlen($string) == 1)
      return $count;
    for ($i=1; $i<strlen($string); $i++)
      // if a new word start count
    if (isset($t[$string[$i-1]]) && !isset($t[$string[$i]])) $count++;
    return $count;
  } // countWords()

  /**
   * Generate a random password of $length
   *
   * @param integer $length
   * @return string password
   */
  public static function generatePassword($length=12) {
    $password = '';
    $salt = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz123456789';
    srand((double) microtime() * 1000000);
    for ($i=0; $i < $length; $i++) {
      $num = rand() % 33;
      $tmp = substr($salt, $num, 1);
      $password .= $tmp;
    }
    return $password;
  } // generatePassword()

  /**
   * Check if the desired $path exists and try to create it also with nested
   * subdirectories if $create is true
   *
   * @param string $path
   * @param boolean $create try to create the directory
   * @return boolean
   */
  public function checkDirectory($path, $create=true) {
    global $I18n;

    if (!file_exists($path)) {
      if ($create) {
        if (!mkdir($path, 0755, true)) {
          $this->setError($I18n->translate("Can't create the directory <b>{{ directory }}</b>!",
              array('directory', $path)), __METHOD__, __LINE__);
          return false;
        }
      }
      else {
        $this->setError($I18n->translate('The directory {{ directory }} does not exists!',
            array('directory' => $path)), __METHOD__, __LINE__);
        return false;
      }
    }
    return true;
  } // checkPath()

  /**
   * Delete a directory recursivly
   *
   * @param string $directory_path
   */
  public function deleteDirectory($directory_path) {
    global $I18n;

    if (is_dir($directory_path)) {
      $items = scandir($directory_path);
      foreach ($items as $item) {
        if (($item != '.') && ($item != '..')) {
          if (filetype($directory_path.'/'.$item) == 'dir')
            $this->deleteDirectory($directory_path.'/'.$item);
          elseif (!unlink($directory_path.'/'.$item)) {
            $this->setError($I18n->translate("Can't delete the file {{ file }}.",
                array('file' => $directory_path.'/'.$item)), __METHOD__, __LINE__);
            return false;
          }
        }
      }
      reset($items);
      if (!rmdir($directory_path)) {
        $this->setError($I18n->translate("Can't delete the directory {{ directory }}.",
            array('directory' => $directory_path)), __METHOD__, __LINE__);
        return false;
      }
    }
    return true;
  } // deleteDirectory()

  /**
   * Converts human readable file size (e.g. 10 MB, 200.20 GB) into bytes.
   *
   * @param string $str
   * @return int the result is in bytes
   * @author Svetoslav Marinov
   * @author http://slavi.biz
   */
  function filesize2bytes($str) {
    $bytes = 0;
    $bytes_array = array(
        'B' => 1,
        'KB' => 1024,
        'MB' => 1024 * 1024,
        'GB' => 1024 * 1024 * 1024,
        'TB' => 1024 * 1024 * 1024 * 1024,
        'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
    );
    $bytes = floatval($str);
    if (preg_match('#([KMGTP]?B)$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) {
      $bytes *= $bytes_array[$matches[1]];
    }
    $bytes = intval(round($bytes, 2));
    return $bytes;
  } // filesize2bytes()

} // class Toolbox