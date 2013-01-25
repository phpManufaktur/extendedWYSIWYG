<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Data;

/**
 * Parse file for PHP TOKENS
 *
 */
class parseTokens {

  /**
   * Check if a token is a CONSTANT
   *
   * @param string $token
   * @return boolean
   */
  protected static function is_constant($token) {
    return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
    $token == T_LNUMBER || $token == T_DNUMBER;
  } // is_constant()

  /**
   * Strip a token value
   *
   * @param string $value
   * @return mixed
   */
  protected static function strip($value) {
    return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
  } // strip()

  /**
   * Parse a file for the given DEFINE PHP tokens
   *
   * @param string $file_path
   * @return multitype:NULL
   */
  public function parseFileForDefines($file_path) {
    // array for the definitions
    $defines = array();

    // values for the loop
    $state = 0;
    $key = '';
    $value = '';

    // get the file content as string
    $file = file_get_contents($file_path);
    // get all PHP tokens from the string
    $tokens = token_get_all($file);
    // reset the tokens
    $token = reset($tokens);

    // loop through the tokens
    while (false !== $token) {
      if (is_array($token)) {
        if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
          // do nothing
        }
        else if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
          $state = 1;
        }
        else if ($state == 2 && self::is_constant($token[0])) {
          $key = $token[1];
          $state = 3;
        }
        else if ($state == 4 && self::is_constant($token[0])) {
          $value = $token[1];
          $state = 5;
        }
      }
      else {
        $symbol = trim($token);
        if ($symbol == '(' && $state == 1) {
          $state = 2;
        }
        else if ($symbol == ',' && $state == 3) {
          $state = 4;
        }
        else if ($symbol == ')' && $state == 5) {
          $defines[self::strip($key)] = self::strip($value);
          $state = 0;
        }
      }
      $token = next($tokens);
    }
    return $defines;
  } // parseFileForDefines()

} // class parseTokens