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
    '[ {{ file }} ] Missing essential parameters!'
      => '[ {{ file }} ] Wesentliche Parameter wurden nicht übermittelt! Bitte nehmen Sie Kontakt mit dem Support auf.',
    'A change of a WYSIWYG section will update the last modified field of the page (recommend!)'
      => 'Die Änderung eines WYSIWYG Abschnitt löst die Aktualisierung des "zuletzt geändert" Feldes der Seite aus (empfohlen!)',
    'ACTIVE'
      => 'Aktiv',
    'Actual Message'
      => 'Aktuelle Meldung',
    'Add department'
      => 'Ressort hinzufügen',
    'Add editor'
      => 'Redakteur hinzufügen',
    'Additional information for approval'
      => 'Zusätzliche Informationen für die Supervisoren',
    'Approve publishing'
      => 'Freigabe',
    '<p>At least you must specify one department for your editorial team.</p>'
      => 'Sie müssen mindestens ein Ressort festlegen, dem Sie die dann die Redakteure zuordnen können.',

    'Be careful: you are allowed to publish this section without any approval!'
      => 'Seien Sie verantwortungsvoll: Sie können diesen Abschnitt ohne eine Prüfung durch Supervisoren direkt freigeben!',

    '<p>Can\'t get the department ID for page ID {{ page_id }}.</p>'
      => '<p>Konnte die Ressort ID für die Seite mit der ID {{ page_id }} nicht ermitteln!</p>',
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

    'DELETED'
      => 'Gelöscht',
    'Deleted the page with the ID {{ id }} because it contains no further sections!'
      => 'Die Seite mit der ID {{ id }} wurde gelöscht, da sie keine weiteren Abschnitte enthält und leer ist.',
    'Deleted the section with the ID {{ id }} from the sections table!'
      => 'Der Abschnitt mit der ID {{ id }} wurde aus der WYSIWYG Tabelle gelöscht.',
    'Department description'
      => 'Ressort Beschreibung',
    'Department list'
      => 'Ressort Liste',
    'Department name'
      => 'Ressort Bezeichnung',
    'Departments'
      => 'Ressorts',

    '<p>Each Trainee must have one or more Supervisors, please check your settings!</p>'
      => '<p>Ein Volontär muss über mindestens einen Supervisor verfügen, der ihn kontrolliert. Bitte prüfen Sie Ihre Einstellungen!</p>',
    'Edit department'
      => 'Ressort bearbeiten',
    'Edit editor'
      => 'Redakteur bearbeiten',
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

    'From'
      => 'Von',

    'General'
      => 'Allgemein',
    'Got a invalid ID for the editor!'
      => 'Ungültige ID für das Bearbeiten eines Redakteurs!',

    'Hide Section'
      => 'Abschnitt einklappen',

    'If activated extendedWYSIWYG will create a protected directory in the /MEDIA path and create a HTML page of each content that get the status BACKUP. The embedded images will be also saved.'
      => 'Falls aktiviert, wird extendedWYSIWYG ein geschütztes Verzeichnis im /MEDIA Ordner anlegen und HTML Dateien für jeden Inhalt anlegen, der den Status BACKUP erhält. Die eingebundenen Bilder werden ebenfalls gesichert.',
    '<p>If something is not working proper, please switch the error message to <b>E_ALL</b> and the errorlog level to <b>DEBUG</b>.</p><p>Execute the process again, return back to this dialog.</p><p>Check the logfile below and report all informations you can gather from this dialog to the <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Group</a>.</p>'
      => '<p>Sollten während des Betriebs Probleme auftreten, dann schalten Sie bitte die Fehlermeldungen auf <b>E_ALL</b> und das Fehlerprotokoll auf <b>DEBUG</b>.</p><p>Wiederholen Sie den Vorgang, bei dem Probleme auftreten und kehren Sie anschließend zu diesem Dialog zurück.</p><p>Überprüfen Sie das unten angezeigte Fehlerprotokoll und wenden Sie sich mit allen Informationen von dieser Seite sowie einer Fehlerbeschreibung an die <a href="https://phpmanufaktur.de/support" target="_blank">Addons Support Group</a>.</p>',
    '<p>It seems you are just starting.</p><p>Please create a <b>department</b> first. If you have at minimum one department next create a <b>chief editor</b> and then begin to create your editor teams.</p>'
      => '<p>Es sieht so aus, als ob Sie das Redaktionssystem das erste Mal aufrufen.</p><p>Beginnen Sie damit, dass Sie zunächst ein <b>Ressort</b> festlegen.</p><p>Nachdem Sie mindestens ein Ressort erstellt haben ernennen Sie als nächstes einen <b>Chefredakteur</b>. Danach können Sie beginnen, die Redaktionsteams zu bilden.</p>',

    'LOCKED'
      => 'Gesperrt',
    '<p>Logged out from extendedWYSIWYG.</p>'
      => '<p>Sie wurden von den Einstellungen für extendedWYSIWYG abgemeldet.</p>',
    'Login'
      => 'Anmeldung',

    'max. Archives in Selection'
      => 'max. Archive in der Auswahl',
    'max. page levels'
      => 'Max. Seitenebenen',
    'Message'
      => 'Mitteilung',
    '[ {{ file }} ] Missing essential parameters!'
      => '[ {{ file }} ] Es fehlen wesentliche Programmparameter!',
    'Message from {{ editor }}'
      => 'Mitteilung von {{ editor }}',

    '<p>Nothing changed.</p>'
      => '<p>Keine Änderungen.</p>',
		
		'Ooops, missing a valid release method! Please contact the support!'
			=> 'Oh, da ist etwas schiefgelaufen: keine geeignete Freigabemethode verfügbar, bitte kontaktieren Sie den Support!',

    'Page Description'
      => 'Beschreibung',
    'Page Keywords'
      => 'Schlüsselwörter',
    'Page Settings'
      => 'Seiteneinstellungen',
    '<p>Page settings for the page with ID {{ page_id }} successfull updated.</p>'
      => '<p>Die Einstellungen für die Seite mit der ID {{ page_id }} wurden erfolgreich geändert.</p>',
    'Page Title'
      => 'Seitentitel',
    'Password'
      => 'Passwort',
    'Permission'
      => 'Berechtigung',
    'Permissions'
      => 'Berechtigungen',
    '<p>Please approve this section for publishing!</p>'
      => 'Bitte überprüfen Sie diesen Abschnitt und entscheiden Sie, ob er auf der Website veröffentlicht werden kann.',
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
    'proofread'
      => 'Korrektur erforderlich',
    'publish'
      => 'veröffentlichen',
    'Publish section'
      => 'Abschnitt veröffentlichen',

    'read more ...'
      => 'Weiterlesen ...',
    'refused'
      => 'zurückgewiesen',
    'Release by own'
      => 'Publizieren',
    'RELEASE_BY_OWN'
      => 'Eigenständige Freigabe',
    'RELEASE_CHIEF_EDITOR_ONLY'
      => 'Freigabe nur durch Chefredakteur',
    'RELEASE_ONE_SUPERVISOR'
      => 'Freigabe gemeinsam mit einem Supervisor',
    'RELEASE_TWO_SUPERVISOR'
      => 'Freigabe gemeinsam mit zwei Supervisoren',
    '<p>Removed department id {{ id }} from the editor {{ name }}.</p>'
      => '<p>Das Ressort mit der ID {{ id }} wurde aus dem Datensatz des Redakteurs {{ name }} entfernt.</p>',
    'Request the approval for release of this section by your supervisors'
      => 'Beantragen Sie die Freigabe dieses Abschnitts durch die für Sie zuständigen Supervisoren',
    'Requiring approval for publishing content'
      => 'Freigabe für die Veröffentlichung benötigt',

    'Save'
      => 'Speichern',
    '<p>Saved the section as archive file {{ archive_file }}.</p>'
      => '<p>Der Abschnitt wurde als Archiv-Datei {{ archive_file }} gesichert.</p>',
    'SECTION_CREATE'
      => 'Abschnitt erstellen',
    'SECTION_DELETE'
      => 'Abschnitt löschen',
    'SECTION_EDIT'
      => 'Abschnitt bearbeiten',
    'SECTION_LOCK'
      => 'Abschnitt sperren',
    'SECTION_RELEASE'
      => 'Abschnitt freigeben',
    'SECTION_VIEW'
      => 'Abschnitt lesen',
    'Section approved'
      => 'Abschnitt geprüft',
    'Section published'
      => 'Abschnitt veröffentlicht',
    'Section rejected'
      => 'Abschnitt zurückgewiesen',
    'Select root page'
      => 'Stammseite auswählen',
    'Select user'
      => 'CMS Benutzer auswählen',
    'Send a email to the other editors of this department'
      => 'Schicken Sie eine E-Mail an die Redakteure dieses Ressorts',
    'Show Page Settings'
      => 'Seiteneinstellungen anzeigen',
		'Sorry, you are not allowed to release this article!'
			=> 'Entschuldigung, aber Sie sind leider nicht befugt diesen Artikel freizugeben!',
    'Specify if you want to use the Editorial department system of extendedWYSIWYG or not. Please read the documentation before you activate the Editorial department!'
      => 'Legen Sie fest, ob Sie das Redaktionssystem von extendedWYSIWYG verwenden möchten oder nicht. Bitte lesen Sie unbedingt die Dokumentation bevor Sie das System aktivieren!',
    'SUB_CHIEF_EDITOR'
      => 'stv. Chefredakteur',
    'Submit'
      => 'Übermitteln',
    '<p>Successfull deleted the editor with the name {{ name }}.</p>'
      => '<p>Der Redakteur mit dem Bezeichner {{ name }} wurde aus dem Redaktionsteam entfernt.</p>',
    '<p>Successfull loaded the archive with the ID {{ archive_id }}.</p>'
      => '<p>Die Archiv Datei mit der ID {{ archive_id }} wurde in den Editor geladen.</p>',
    'Supervisors'
      => 'Supervisoren',

    '<p>The content of the section <b>{{ section_id }}</b> has not changed, so nothing was to save.</p>'
      => '<p>Der Inhalt des Abschnitts <b>{{ section_id }}</b> wurde nicht verändert und deshalb auch nicht gespeichert.</p>',
    '<p>The content for the SECTION ID {{ section_id }} was successfull saved.</p>'
      => '<p>Der Inhalt des Abschnitt mit der ID {{ section_id }} wurde gesichert.</p>',
    '<p>The department with the ID {{ id }} was deleted.</p>'
      => '<p>Das Ressort mit der ID {{ id }} wurde gelöscht.</p>',
    '<p>The department {{ name }} was successfull inserted.</p>'
      => '<p>Das Ressort {{ name }} wurde hinzugefügt.</p>',
    '<p>The editor <b>{{ editor }}</b> ask you to approve this section for publishing.</p>'
      => '<p>Der Redakteur <b>{{ editor }}</b> bittet Sie um Prüfung dieses Abschnittes für eine Veröffentlichung.</p>',
    '<p>The editor {{ name }} is not assigned to a department, is this correct?</p>'
      => '<p>Der Redakteur {{ name }} is keinem Ressort zugeteilt, ist das korrekt?</p>',
    '<p>The editor must have the permission to release articles by his own or you must assign one or more supervisors to the editor!</p>'
      => '<p>Der Redakteur muss entweder über das Recht verfügen Artikel eigenständig freizugeben oder es müssen ihm ein oder mehrere Supervisore für die Freigabe zugeordnet werden.</p><p>Bitte prüfen Sie die Einstellungen für den Redakteur!</p>',
    '<p>The editor {{ name }} was successfull updated.</p>'
      => '<p>Der Datensatz für den Redakteur {{ name }} wurde aktualisiert.</p>',
    '<p>The email program needs a configured <b>smtp</b> access.</p><p>Please check the settings of your CMS!</p>'
      => '<p>Das E-Mail Programm benötigt einen konfigurierten <b>SMTP</b> Zugriff.</p><p>Bitte prüfen Sie die Einstellungen Ihres CMS!</p>',
    '<p>The error level is successfull changed to {{ level }}.</p>'
      => '<p>Die Stufe für die Fehlermeldungen wurde auf {{ level }} geändert.</p>',
    '<p>The LOGFILE does not exists!</p>'
      => 'Es existiert keine LOG Datei.',
    '<p>The loglevel is successfull changed to {{ level }}</p>'
      => '<p>Die Protokollierung wurde zur Stufe {{ level }} geändert.</p>',
    '<p>The page with the ID {{ id }} does no longer exists and was removed from record of {{ name }}.</p>'
      => '<p>Die Seite mit der PAGE ID {{ id }} existiert nicht mehr und wurde deshalb aus dem Datensatz von {{ name }} entfernt.</p>',
    '<p>The page with the ID {{ id }} for the root parent of the department {{ name }} does no longer exists!</p><p>The department will be locked, please assign a new root parent!</p>'
      => '<p>Die Stammseite mit der ID {{ id }} für das Ressorts {{ name }} existiert nicht mehr!</p><p>Das Ressort wurde gesperrt, bitte ordnen Sie dem Ressort eine neue Stammseite zu!</p>',
    '<p>The page with the id {{ id }} no longer belongs to the departments and was removed from the record of {{ name }}</p>'
      => '<p>Die Seite mit der PAGE ID {{ id }} gehört nicht mehr zu den Ressorts und wurde deshalb aus dem Datensatz von {{ name }} entfernt.</p>',
    'The maximum number of archives that will be shown in the selection list'
      => 'Die maximale Anzahl von Archiven, die in der Auswahlliste angezeigt wird (Voreinstellung: 10)',
    'The maximum page levels shown in the selection list for the department root parent'
      => 'Die maximale Anzahl von Seitenebenen, die in der Auswahlliste für die Ressorts angezeigt werden (Vorgabe 1)',
    '<p>The section id {{ section_id }} is marked for approval by your supervisors {{ supervisors }}.</p><p>Look ahead for further informations!</p>'
      => '<p>Der Abschnitt mit der ID {{ section_id }} ist für die Prüfung zur Freigabe durch die Supervisoren {{ supervisors }} gekennzeichnet und deshalb vorläufig gesperrt.</p><p>Warten sie auf weitere Informationen!</p>',
    '<p>The section with the ID {{ section_id }} was successfull published!</p>'
      => '<p>Der Abschnitt mit der ID {{ section_id }} wurde veröffentlicht!</p>',
    '<p>Page settings for the page with ID {{ page_id }} successfull updated.</p>'
      => '<p>Die Seiteneinstellungen für die Seite mit der ID {{ page_id }} wurden aktualisiert.</p>',
    '<p>The teaser for the page ID {{ page_id }} was successfull saved.</p>'
      => '<p>Der Anreisser für die Seite mit der ID {{ page_id ]] wurde gesichert.</p>',
    '<p>The page teaser was successfully updated.</p>'
      => '<p>Die <b>aktuelle Meldung</b> für die Seite wurde aktualisiert.</p>',
    '<p>The section <b>{{ section_id }}</b> was successfull saved.</p>'
      => '<p>Die Section <b>{{ section_id }}</b> wurde erfolgreich gespeichert.</p>',
    '<p>This section is protected by extendedWYSIWYG.</p><p>Due the actual settings you are not allowed to access the content of this section.</p><p>Please contact your webmaster if you are of another opinion.</p>'
      => '<p>Dieser Abschnitt wird durch <b>extendedWYSIWYG</b> geschützt.</p><p>Die aktuellen Einstellungen verbieten Ihnen den Zugriff auf diesen Bereich.</p><p>Wenden Sie sich bitte an Ihren Webmaster, falls Sie eine Zugangsberechtigung erhalten möchten.</p>',
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

    '<p>You are not allowed to add/create a new section!</p>'
      => '<p>Sie sind nicht berechtigt einen neuen Abschnitt zu erstellen oder hinzuzufügen!</p>',
    '<p>You are not allowed to delete the section with the ID {{ section_id }}!</p>'
      => '<p>Sie sind nicht berechtigt, den Abschnitt mit der ID {{ section_id }} zu löschen!</p>',
    '<p>You must login as administrator to get access to the extendedWYSIWYG settings.</p>'
      => '<p>Sie müssen sich mit Administratorrechten anmelden, um Zugriff auf die extendedWYSIWYG Einstellungen zu erhalten.</p>',
    'Your message'
      => 'Ihre Mitteilung',
    '<p>Your message was send to the members of the department!</p>'
      => '<p>Ihre Mitteilung wurde an die Mitglieder des Ressorts versendet!</p>'
);
