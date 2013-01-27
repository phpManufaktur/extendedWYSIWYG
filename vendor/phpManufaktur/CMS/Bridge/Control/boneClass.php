<?php

/**
 * cmsBridge
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CMS\Bridge\Control;

global $logger;

class boneClass {

  private static $error = '';
  private static $message = '';

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
  public function setError($error, $method, $line) {
    global $logger;
    self::$error = sprintf('[%s - %s] %s', $method, $line, $error);
    $logger->addError(strip_tags(self::$error));
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
   * Reset the error status
   *
   */
  public function resetError() {
    self::$error = '';
  } // resetError()

  /**
   * Return the a message
   *
   * @return string
   */
  public function getMessage() {
    return self::$message;
  } // getMessage()

  /**
   * Reset the messages
   *
   */
  public function resetMessage() {
    self::$message = '';
  } // resetMessage()

  /**
   * Set a message and add it to the logger.
   * The new message will be apend to the former message
   *
   * @param string $message
   * @param string $method
   * @param string $line
   */
  protected function setMessage($message, $method, $line) {
    global $logger;
    self::$message .= $message;
    $logger->addInfo(sprintf('[%s - %s] %s', $method, $line, strip_tags($message)));
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
