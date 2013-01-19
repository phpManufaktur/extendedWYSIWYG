<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH'))
  include __DIR__.'/bootstrap.php';

global $database;

$checked = true;

// check PHP version
$PRECHECK['PHP_VERSION'] = array(
    'VERSION' => '5.2.0',
    'OPERATOR' => '>='
);

// modified precheck array
$check = array(
    'Dwoo' => array(
        'directory' => 'dwoo',
        'version' => '0.16',
        'problem' => 'Dwoo => <b><a href="https://addons.phpmanufaktur.de/download.php?file=Dwoo" target="_blank">Download actual version</a></b>'
    ),
    'dropletsExtension' => array(
        'directory' => 'droplets_extension',
        'version' => '0.24',
        'problem' => 'dropletsExtension => <b><a href="https://addons.phpmanufaktur.de/download.php?file=dropletsExtension" target="_blank">Download actual version</a></b>'
    ),
    'manufakturConfig' => array(
        'directory' => 'manufaktur_config',
        'version' => '0.16',
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
