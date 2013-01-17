<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
  // important: language files must be saved as UTF-8 (without BOM)
  trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

$LANG = array(
    'A change of a WYSIWYG section will update the last modified field of the page (recommend!)'
      => 'Die Änderung eines WYSIWYG Abschnitt löst die Aktualisierung des "zuletzt geändert" Feldes der Seite aus (empfohlen!)',
    'Chars'
      => 'Zeichen',
    'Create Archive Files'
      => 'Archivdateien anlegen',
    'Error executing the template <b>{{ template }}</b>: {{ error }}'
      => 'Fehler bei der Ausführung des Template <b>{{ template }}</b>: {{ error }}',
    "Error: Can't create the directory <b>{{ directory }}</b>!"
      => 'Konnte das Verzeichnis <b>{{ directory }}</b> nicht anlegen!',
    "Error: Can't create the <b>{{ file }}</b> file for the protected WYSIWYG folder!"
      => 'Konnte die <b>{{ file }}</b> Datei für das geschützte WYSIWYG Verzeichnis nicht erstellen!',
    "Error: Can't write the file <b>{{ file }}</b>!"
      => 'Konnte die Datei <b>{{ file }}</b> nicht schreiben!',
    'Error: Missing the ARCHIVE_ID for section <b>{{ section_id }}</b>!'
      => 'Vermisse die ARCHIVE_ID für die Section <b>{{ section_id }}</b>!',
    'Error: Missing the WYSIWYG content for section <b>{{ section_id }}</b>!'
      => 'Vermisse den WYSIWYG Inhalt der Section <b>{{ section_id }}</b>!',
    'Error: The ARCHIVE_ID <b>{{ archive_id }}</b> does not exists!'
      => 'Die ARCHIVE_ID <b>{{ archive_id }}</b> existiert nicht!',
    'Hide Section'
      => 'Abschnitt einklappen',
    'If activated extendedWYSIWYG will create a protected directory in the /MEDIA path and create a HTML page of each content that get the status BACKUP. The embedded images will be also saved.'
      => 'Falls aktiviert, wird extendedWYSIWYG ein geschütztes Verzeichnis im /MEDIA Ordner anlegen und HTML Dateien für jeden Inhalt anlegen, der den Status BACKUP erhält. Die eingebundenen Bilder werden ebenfalls gesichert.',
    'max. Archives in Selection'
      => 'max. Archive in der Auswahl',
    'Page Description'
      => 'Beschreibung',
    'Page Keywords'
      => 'Schlüsselwörter',
    'Page Settings'
      => 'Seiteneinstellungen',
    'Page Title'
      => 'Seitentitel',
    'Please help to improve Open Source Software and report this problem to the <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Group.'
      => 'Bitte helfen Sie mit Open Source Software zu verbessern und melden Sie dieses Problem der <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Gruppe.',
    'publish'
      => 'veröffentlichen',
    'read more ...'
      => 'Weiterlesen ...',
    'Save'
      => 'Speichern',
    'Show Page Settings'
      => 'Seiteneinstellungen anzeigen',
    '<p>The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.</p>'
      => '<p>Der Inhalt der Section <b>{{ section_id }}</b> wurde nicht verändert und deshalb auch nicht gespeichert.</p>',
    'The maximum number of archives that will be shown in the selection list'
      => 'Die maximale Anzahl von Archiven, die in der Auswahlliste angezeigt wird (Voreinstellung: 10)',
    '<p>The page settings has been updated.</p>'
      => '<p>Die Seiteneinstellungen wurden aktualisiert.</p>',
    '<p>The page teaser was successfully updated.</p>'
      => '<p>Die <b>aktuelle Meldung</b> für die Seite wurde aktualisiert.</p>',
    '<p>The section <b>{{ section_id }}</b> was successfull saved.</p>'
      => '<p>Die Section <b>{{ section_id }}</b> wurde erfolgreich gespeichert.</p>',
    'Update page information'
      => 'Seiten aktualisieren',
    'Actual Message'
      => 'Aktuelle Meldung',
    'Words'
      => 'Wörter'
);
