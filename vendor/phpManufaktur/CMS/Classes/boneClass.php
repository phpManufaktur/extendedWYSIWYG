<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Classes;

global $logger;

class boneClass {

  private static $error;
  private static $message;

  /**
   * Return the last error
   *
   * @return string
   */
  public function getError() {
    return self::$error;
  } // getError()

  /**
   * Set the error and log it with Logger
   *
   * @param string $error
   * @param string $method
   * @param string $line
   */
  protected function setError($error, $method, $line) {
    global $logger;
    self::$error = sprintf('[%s - %s] %s', $method, $line, $error);
    $logger->addError(self::$error);
  } // setError()

  /**
   * Check if a error message exists
   *
   * @return boolean
   */
  public function isError() {
    return (bool) (!empty(self::$error));
  } // isError()

  /**
   * Return the a message
   *
   * @return string
   */
  public function getMessage() {
    return self::$message;
  } // getMessage()

  /**
   * Set a message and add it to the logger
   *
   * @param string $message
   * @param string $method
   * @param string $line
   */
  protected function setMessage($message, $method, $line) {
    global $logger;
    self::$message = $message;
    $logger->addInfo(sprintf('[%s - %s] %s', $method, $line, $message));
  } // setMessage()

  /**
   * Check if a message exists
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) (!empty(self::$message));
  } // isMessage()

  /**
   * put a information to the logger
   *
   * @param string $info
   * @param string $method
   * @param string $line
   */
  protected function setInfo($info, $method, $line) {
    global $logger;
    $logger->addInfo(sprintf('[%s - %s] %s', $method, $line, $info));
  }

} // boneClass
