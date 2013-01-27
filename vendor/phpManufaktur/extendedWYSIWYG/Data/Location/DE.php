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
  // BEWARE: the language files must be saved as UTF-8 (without BOM)
  trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

$LANG = array(
    '- please select -'
      => '- bitte auswählen -',
    '- Unique root page for all editors -'
      => 'Universelle Stammseite für alle Redakteure',
    'A change of a WYSIWYG section will update the last modified field of the page (recommend!)'
      => 'Die Änderung eines WYSIWYG Abschnitt löst die Aktualisierung des "zuletzt geändert" Feldes der Seite aus (empfohlen!)',
    'Actual Message'
      => 'Aktuelle Meldung',
    'Add department'
      => 'Ressort hinzufügen',
    'Add editor'
      => 'Redakteur hinzufügen',
    '<p>At least you must specify one department for your editorial team.</p>'
      => 'Sie müssen mindestens ein Ressort festlegen, dem Sie die dann die Redakteure zuordnen können.',

    'Change'
      => 'Ändern',
    'Change department'
      => 'Ressort ändern',
    'Chars'
      => 'Zeichen',
    'CHIEF_EDITOR'
      => 'Chefredakteur',
    'Create Archive Files'
      => 'Archivdateien anlegen',

    'Department description'
      => 'Ressort Beschreibung',
    'Department list'
      => 'Ressort Liste',
    'Department name'
      => 'Ressort Bezeichnung',
    'Departments'
      => 'Ressorts',

    'Edit department'
      => 'Ressort bearbeiten',
    'Editor'
      => 'Redakteur',
    'EDITOR'
      => 'Redakteur',
    'Editor list'
      => 'Liste der Redakteure',
    'Editorial department'
      => 'Redaktion',
    'Editorial team'
      => 'Redakteure',
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
    'Error messages, level'
      => 'Fehlermeldungen, Stufe',
    'Errorlog'
      => 'Fehler- & Ereignisprotokoll',
    'Errorlog, level'
      => 'Fehlerprotokoll, Stufe',

    'General'
      => 'Allgemein',
    'Got a invalid ID for the editor!'
      => 'Ungültige ID für das Bearbeiten eines Redakteurs!',

    'Hide Section'
      => 'Abschnitt einklappen',

    'If activated extendedWYSIWYG will create a protected directory in the /MEDIA path and create a HTML page of each content that get the status BACKUP. The embedded images will be also saved.'
      => 'Falls aktiviert, wird extendedWYSIWYG ein geschütztes Verzeichnis im /MEDIA Ordner anlegen und HTML Dateien für jeden Inhalt anlegen, der den Status BACKUP erhält. Die eingebundenen Bilder werden ebenfalls gesichert.',
    '<p>If something is not working proper, please switch the error message to <b>E_ALL</b> and the errorlog level to <b>DEBUG</b>.</p><p>Execute the process again, return back to this dialog.</p><p>Check the logfile below and report all informations you can gather from this dialog to the <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Group</a>.</p>'
      => '<p>Sollten währen des Betriebs Probleme auftreten, dann schalten Sie bitte die Fehlermeldungen auf <b>E_ALL</b> und das Fehlerprotokoll auf <b>DEBUG</b>.</p><p>Wiederholen Sie den Vorgang, bei dem Probleme auftreten und kehren Sie anschließend zu diesem Dialog zurück.</p><p>Überprüfen Sie das unten angezeigte Fehlerprotokoll und wenden Sie sich mit allen Informationen von dieser Seite sowie einer Fehlerbeschreibung an die <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Group</a>.</p>',

    '<p>Logged out from extendedWYSIWYG.</p>'
      => '<p>Sie wurden von den Einstellungen für extendedWYSIWYG abgemeldet.</p>',
    'Login'
      => 'Anmeldung',

    'max. Archives in Selection'
      => 'max. Archive in der Auswahl',
    'max. page levels'
      => 'Max. Seitenebenen',

    'Page Description'
      => 'Beschreibung',
    'Page Keywords'
      => 'Schlüsselwörter',
    'Page Settings'
      => 'Seiteneinstellungen',
    'Page Title'
      => 'Seitentitel',
    'Password'
      => 'Passwort',
    '<p>Please determine at least one department for your editorial team.</p><p>In the case you want to edit all pages of your website with the same team, set the root page below to "Unique root for all editors".</p><p>In all other cases select a page as root page for the department. All pages below this one will be accessible for the edtiors belonging to this department.</p><p>Departments can\'t be nested!</p>'
      => '<p>Legen Sie mindestens ein Ressort für Ihr Redaktionsteam fest.</p></p>Für den Fall, dass Sie alle Seiten Ihrer Website mit dem gleichen Team bearbeiten und organisieren möchten, setzen Sie die Stammseite auf "Universelle Stammseite für alle Redakteure".</p><p>In allen anderen Fällen wählen Sie eine beliebige Seite als Stammseite für das Ressort aus.</p><p>Ressorts können nicht ineinander verschachtelt werden!</p>',
    '<p>Please edit the department with the ID {{ id }}.</p>'
      => 'Bearbeiten Sie das Ressort mit der ID {{ id }}.</p>',
    '<p>Please edit the editor with the ID {{ id }}.</p>'
      => '<p>Bearbeiten Sie die Angaben zu dem Redakteur mit der ID {{ id }}.</p>',
    'Please help to improve Open Source Software and report this problem to the <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Group.'
      => 'Bitte helfen Sie mit Open Source Software zu verbessern und melden Sie dieses Problem der <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Gruppe.',
    '<p>Please select a root page and a name for the new department!</p>'
      => '<p>Bitte wählen Sie eine Stammseite auns und geben Sie dem neuen Ressort einen Namen!</p>',
    '<p>Please type in your username and password!</p>'
      => '<p>Bitte geben Sie Ihren Benutzernamen und Ihr Passwort an!</p>',
    'publish'
      => 'veröffentlichen',

    'read more ...'
      => 'Weiterlesen ...',

    'Save'
      => 'Speichern',
    'Select root page'
      => 'Stammseite auswählen',
    'Select user'
      => 'CMS Benutzer auswählen',
    'Show Page Settings'
      => 'Seiteneinstellungen anzeigen',
    'Specify if you want to use the Editorial department system of extendedWYSIWYG or not. Please read the documentation before you activate the Editorial department!'
      => 'Legen Sie fest, ob Sie das Redaktionssystem von extendedWYSIWYG verwenden möchten oder nicht. Bitte lesen Sie unbedingt die Dokumentation bevor Sie das System aktivieren!',
    'SUB_CHIEF_EDITOR'
      => 'stv. Chefredakteur',

    '<p>The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.</p>'
      => '<p>Der Inhalt der Section <b>{{ section_id }}</b> wurde nicht verändert und deshalb auch nicht gespeichert.</p>',
    '<p>The error level is successfull changed to {{ level }}.</p>'
      => '<p>Die Stufe für die Fehlermeldungen wurde auf {{ level }} geändert.</p>',
    '<p>The loglevel is successfull changed to {{ level }}</p>'
      => '<p>Die Protokollierung wurde zur Stufe {{ level }} geändert.</p>',
    'The maximum number of archives that will be shown in the selection list'
      => 'Die maximale Anzahl von Archiven, die in der Auswahlliste angezeigt wird (Voreinstellung: 10)',
    'The maximum page levels shown in the selection list for the department root parent'
      => 'Die maximale Anzahl von Seitenebenen, die in der Auswahlliste für die Ressorts angezeigt werden (Vorgabe 1)',
    '<p>The page settings has been updated.</p>'
      => '<p>Die Seiteneinstellungen wurden aktualisiert.</p>',
    '<p>The page teaser was successfully updated.</p>'
      => '<p>Die <b>aktuelle Meldung</b> für die Seite wurde aktualisiert.</p>',
    '<p>The section <b>{{ section_id }}</b> was successfull saved.</p>'
      => '<p>Die Section <b>{{ section_id }}</b> wurde erfolgreich gespeichert.</p>',
    'TRAINEE'
      => 'Volontär',

    'Update page information'
      => 'Seiten aktualisieren',
    'Use Editorial department'
      => 'Redaktionssystem verwenden',
    'Username'
      => 'Benutzername',

    '<p>Welcome to the settings for extendWYSIWYG.</p>'
      => '<p>Herzlich willkommen im Konfigurationsdialog für extendedWYSIWYG.</p>',
    'Words'
      => 'Wörter',

    '<p>You must login as administrator to get access to the extendedWYSIWYG settings.</p>'
      => '<p>Sie müssen sich mit Administratorrechten anmelden, um Zugriff auf die extendedWYSIWYG Einstellungen zu erhalten.</p>'
);
