<?php

/**
 * extendedWYSIWYG
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

if ('á' != "\xc3\xa1") {
	// important: language files must be saved as UTF-8 (without BOM)
	trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

$LANG = array(
    'Error executing the template <b>{{ template }}</b>: {{ error }}'
      => 'Fehler bei der Ausführung des Template <b>{{ template }}</b>: {{ error }}',
    'Error: Missing the ARCHIVE_ID for section <b>{{ section_id }}</b>!'
      => 'Vermisse die ARCHIVE_ID für die Section <b>{{ section_id }}</b>!',
    'Error: Missing the WYSIWYG content for section <b>{{ section_id }}</b>!'
      => 'Vermisse den WYSIWYG Inhalt der Section <b>{{ section_id }}</b>!',
    'Error: The ARCHIVE_ID <b>{{ archive_id }}</b> does not exists!'
      => 'Die ARCHIVE_ID <b>{{ archive_id }}</b> existiert nicht!',
    'Please help to improve Open Source Software and report this problem to the <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Group.'
      => 'Bitte helfen Sie mit Open Source Software zu verbessern und melden Sie dieses Problem der <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Gruppe.',
    'publish'
      => 'veröffentlichen',
    'Save'
      => 'Speichern',
    'The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.'
      => 'Der Inhalt der Section <b>{{ section_id }}</b> wurde nicht verändert und deshalb auch nicht gespeichert.',
    'The section <b>{{ section_id }}</b> was successfull saved.'
      => 'Die Section <b>{{ section_id }}</b> wurde erfolgreich gespeichert.',
);
