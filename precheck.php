<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (defined('CAT_VERSION')) {
    // BlackCat is not compatible
    $PRECHECK['CUSTOM_CHECKS']['At the moment extendedWYSIWYG is not compatible with BlackCat CMS, please contact the support to get more information!'] = array(
        'REQUIRED' => 'OK',
        'ACTUAL' => 'PROBLEM',
        'STATUS' => false
    );
}
else {
    if (!defined('WB_PATH'))
      include __DIR__.'/bootstrap.php';

    global $database;

    $checked = true;

    // check PHP version
    $PRECHECK['CUSTOM_CHECKS']['PHP Version'] = array(
        'REQUIRED' => '5.3.2',
        'ACTUAL' => PHP_VERSION,
        'STATUS' => version_compare(PHP_VERSION, '5.3.2', '>=')
    );

    // modified precheck array
    $check = array(
        'Dwoo' => array(
            'directory' => 'dwoo',
            'version' => '0.17',
            'problem' => 'Dwoo => <b><a href="https://addons.phpmanufaktur.de/download.php?file=Dwoo" target="_blank">Download actual version</a></b>'
        ),
        'manufakturConfig' => array(
            'directory' => 'manufaktur_config',
            'version' => '0.17',
            'problem' => 'manufakturConfig => <b><a href="https://addons.phpmanufaktur.de/download.php?file=manufakturConfig" target="_blank">Download actual version</a></b>'
            )
    );

    $versionSQL = "SELECT `version` FROM `".TABLE_PREFIX."addons` WHERE `directory`='%s'";

    foreach ($check as $name => $addon) {
      // loop throug the addons and check the versions
      $version = $database->get_one(sprintf($versionSQL, $addon['directory']), MYSQL_ASSOC);
      if (false === ($status = version_compare(!empty($version) ? $version : '0', $addon['version'], '>='))) {
        $checked = false;
        $key = $addon['problem'];
      }
      else
        $key = $name;
      $PRECHECK['CUSTOM_CHECKS'][$key] = array(
          'REQUIRED' => $addon['version'],
          'ACTUAL' => !empty($version) ? $version : '- not installed -',
          'STATUS' => $status
      );
    }

    // check WebsiteBaker / LEPTON
    $wb_version = $database->get_one("SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='wb_version'");
    if (!empty($wb_version)) {
      // WebsiteBaker
      if (!in_array($wb_version, array('2.8.2', '2.8.3'))) {
        $checked = false;
        $key = "The WebsiteBaker version $wb_version is not approved for extendedWYSIYG!";
        $status = false;
      }
      else {
        $key = 'CMS Version';
        $wb_version = 'CMS OK';
        $status = true;
      }
      $PRECHECK['CUSTOM_CHECKS'][$key] = array(
          'REQUIRED' => 'CMS OK',
          'ACTUAL' => $wb_version,
          'STATUS' => $status
      );
    }
    else {
      // check for LEPTON
      $lepton_version = $database->get_one("SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='lepton_version'");
      if (!empty($lepton_version)) {
        // WebsiteBaker
        if (!in_array($lepton_version, array('1.1.4', '1.2.0', '1.2.1'))) {
          $checked = false;
          $key = "The LEPTON CMS version $wb_version is not approved for extendedWYSIYG!";
          $status = false;
        }
        else {
          $lepton_version = 'CMS OK';
          $key = 'CMS Version';
          $status = true;
        }
        $PRECHECK['CUSTOM_CHECKS'][$key] = array(
            'REQUIRED' => 'CMS OK',
            'ACTUAL' => $lepton_version,
            'STATUS' => $status
        );
      }
      else {
        // can't detect the CMS version
        $PRECHECK['CUSTOM_CHECKS']['Can\'t detect the CMS version, please contact the support!'] = array(
            'REQUIRED' => 'CMS OK',
            'ACTUAL' => '- unknown -',
            'STATUS' => false
        );
      }
    }


    // check default charset
    $SQL = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
    $charset = $database->get_one($SQL, MYSQL_ASSOC);
    if ($charset != 'utf-8') {
      $checked = false;
      $key = 'This addon needs UTF-8 as default charset!';
    }
    else
      $key = 'UTF-8';

    $PRECHECK['CUSTOM_CHECKS'][$key] = array(
        'REQUIRED' => 'utf-8',
        'ACTUAL' => $charset,
        'STATUS' => ($charset == 'utf-8')
    );

    if (!$checked) {
      // if a problem occured prompt a hint and grant that the LEPTON/WB precheck fail
      $PRECHECK['CUSTOM_CHECKS']['Please install or update all required addons.<br />Need help? Please contact the <b><a href="https://phpmanufaktur.de/support" target="_blank">phpManufaktur Support Group</a></b>.'] = array(
          'REQUIRED' => 'OK',
          'ACTUAL' => 'PROBLEM',
          'STATUS' => false
      );
    }
}
