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

use phpManufaktur\CMS\Bridge\Data\LEPTON\Settings;

use phpManufaktur\CMS\Bridge\Data;

require_once CMS_ADDON_PATH.'/vendor/SwiftMailer/lib/swift_required.php';
require_once CMS_ADDON_PATH.'/vendor/SwiftMailer/lib/classes/Swift/SwiftException.php';

class swiftMailer extends boneClass {

  protected static $SERVER_EMAIL = null;
  protected static $SERVER_NAME = null;
  protected static $EMAIL_PROTOCOL = null;
  protected static $SMTP_HOST = null;
  protected static $SMTP_PORT = 25;
  protected static $SMTP_AUTH = null;
  protected static $SMTP_USERNAME = null;
  protected static $SMTP_PASSWORD = null;

  protected $mailer = null;

  /**
   * Get the EMail settings from the parent CMS
   *
   * @return boolean
   */
  protected function getEMailSettings() {
    $setting = new Settings();
    self::$SERVER_EMAIL = $setting->select('server_email');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$SERVER_NAME = $setting->select('wbmailer_default_sendername');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$EMAIL_PROTOCOL = $setting->select('wbmailer_routine');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$SMTP_HOST = $setting->select('wbmailer_smtp_host');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$SMTP_AUTH = $setting->select('wbmailer_smtp_auth');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$SMTP_USERNAME = $setting->select('wbmailer_smtp_username');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    self::$SMTP_PASSWORD = $setting->select('wbmailer_smtp_password');
    if ($setting->isError()) {
      $this->setError($setting->getError(), __METHOD__, __LINE__);
      return false;
    }
    return true;
  } // getEMailSettings()

  /**
   * Initialize the SwiftMailer with the SMTP settings
   *
   * @return boolean
   */
  public function init() {
    global $I18n;

    if (!$this->getEMailSettings())
      return false;

    if (self::$EMAIL_PROTOCOL != 'smtp') {
      $this->setError($I18n->translate('<p>The email program needs a configured <b>smtp</b> access.</p><p>Please check the settings of your CMS!</p>'),
          __METHOD__, __LINE__);
      return false;
    }

    try {
      $transport = \Swift_SmtpTransport::newInstance(self::$SMTP_HOST, self::$SMTP_PORT)
      ->setUsername(self::$SMTP_USERNAME)
      ->setPassword(self::$SMTP_PASSWORD);
      $this->mailer = \Swift_Mailer::newInstance($transport);
    } catch (\Swift_SwiftException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  }

  /**
   * Simplified mail method, uses the configured server as sender
   *
   * @param string $subject
   * @param string $body
   * @param string $email_to
   * @return boolean
   */
  public function sendServerMail($subject, $body, $email_to) {
    global $I18n;

    try {
      $mail = \Swift_Message::newInstance();
      $mail->setSubject($subject);
      $mail->setTo($email_to);
      $mail->setFrom(array(self::$SERVER_EMAIL => self::$SERVER_NAME));
      $mail->setBody($body, 'text/html');
      $failures = array();
      if (!$this->mailer->send($mail, $failures)) {
        $this->setError($I18n->translate('<p>Can\'t send the email to {{ email }}.</p><p>Please check the email address and the settings of the email transport.</p>',
            array('email' => implode(', ', $failures))), __METHOD__, __LINE__);
        return false;
      }
    } catch (\Swift_SwiftException $e) {
      $this->setError($e->getMessage(), __METHOD__, $e->getLine());
      return false;
    }
    return true;
  } // sendServerMail()

} // class swiftMailer