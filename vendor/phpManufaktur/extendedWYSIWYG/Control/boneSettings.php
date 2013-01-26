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

use phpManufaktur\CMS\Bridge\Control\boneClass;

class boneSettings extends boneClass {

  const REQUEST_ACTION = 'act';
  const REQUEST_SUB_ACTION = 'sub';
  const REQUEST_USERNAME = 'usr';
  const REQUEST_PASSWORD = 'pwd';
  const REQUEST_LOGFILE_LEVEL = 'lfl';
  const REQUEST_ERROR_LEVEL = 'elv';
  const REQUEST_USER = 'usr';

  const ACTION_DEFAULT = 'def';
  const ACTION_LOGIN = 'lgi';
  const ACTION_LOGIN_CHECK = 'lgic';
  const ACTION_LOGOUT = 'ext';
  const ACTION_SETTINGS = 'set';
  const ACTION_START = 'sta';
  const ACTION_CHANGE_LEVEL = 'chg';
  const ACTION_EDITORIAL = 'edi';
  const ACTION_EDITORIAL_TEAM = 'edt';
  const ACTION_EDITORIAL_DEPARTMENT = 'edd';

  const SESSION_SETTINGS_AUTHENTICATED = 'ssa';
  const SESSION_SETTINGS_USER = 'ssu';

  protected static $LOGGER_LEVEL = CMS_LOGGER_LEVEL;
  protected static $ERROR_LEVELS = array(
      'E_ALL' => '6143',
      'E_ALL^E_NOTICE' => '6135',
      'E_ALL&E_STRICT' => '8191',
      'E_NONE' => '0'
      );

  protected static $TEMPLATE_PATH = null;
  protected static $TEMPLATE_URL = null;
  protected static $SETTINGS_URL = null;


} // class boneSettings