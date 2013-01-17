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

class browserLanguage {

  /**
   * Scan the desired directory for language files and gather them in
   * $available_languages
   *
   * @param string $files_path
   * @param array reference $available_languages
   * @return boolean
   */
  public function getAvailableLanguages($files_path, &$available_languages=array()) {
    if (false === ($files = scandir($files_path))) {
      return false;
    }
    $available_languages = array('en');
    foreach ($files as $file) {
      if (in_array($file, array('.', '..', 'index.php')))
        continue;
      $file = strtolower(pathinfo($file, PATHINFO_FILENAME));
      if (!in_array($file, $available_languages))
        $available_languages[] = $file;
    }
    return true;
  } // getAvailableLanguages()

  /**
   * Try to get the language from the browser.
   * This code is taken from SelfHTML
   *
   * @author Christina Seiler <self@christian-seiler.de>
   * @link http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
   *
   * @param array $allowed_languages Array with the available languages (lowercase)
   * @param string $default_language language to take if no other is available
   * @param string $lang_variable use instead of $_SERVER['HTTP_ACCEPT_LANGUAGE']
   * @param boolean $strict_mode use exactly the HTTP specification
   * @return string language code
   */
  public function get($allowed_languages, $default_language, $lang_variable = null, $strict_mode = true) {
    // $_SERVER['HTTP_ACCEPT_LANGUAGE'] verwenden, wenn keine Sprachvariable mitgegeben wurde
    if ($lang_variable === null) {
      $lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    // wurde irgendwelche Information mitgeschickt?
    if (empty($lang_variable)) {
      // Nein? => Standardsprache zurückgeben
      return $default_language;
    }
    // Den Header auftrennen
    $accepted_languages = preg_split('/,\s*/', $lang_variable);
    // Die Standardwerte einstellen
    $current_lang = $default_language;
    $current_q = 0;
    // Nun alle mitgegebenen Sprachen abarbeiten
    foreach ($accepted_languages as $accepted_language) {
      // Alle Infos über diese Sprache rausholen
      $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
          '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);
      // war die Syntax gültig?
      if (!$res) {
        // Nein? Dann ignorieren
        continue;
      }
      // Sprachcode holen und dann sofort in die Einzelteile trennen
      $lang_code = explode ('-', $matches[1]);
      // Wurde eine Qualität mitgegeben?
      if (isset($matches[2])) {
        // die Qualität benutzen
        $lang_quality = (float)$matches[2];
      } else {
        // Kompabilitätsmodus: Qualität 1 annehmen
        $lang_quality = 1.0;
      }
      // Bis der Sprachcode leer ist...
      while (count ($lang_code)) {
        // mal sehen, ob der Sprachcode angeboten wird
        if (in_array (strtolower (join ('-', $lang_code)), $allowed_languages)) {
          // Qualität anschauen
          if ($lang_quality > $current_q) {
            // diese Sprache verwenden
            $current_lang = strtolower (join ('-', $lang_code));
            $current_q = $lang_quality;
            // Hier die innere while-Schleife verlassen
            break;
          }
        }
        // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
        if ($strict_mode) {
          // innere While-Schleife aufbrechen
          break;
        }
        // den rechtesten Teil des Sprachcodes abschneiden
        array_pop ($lang_code);
      }
    }
    // die gefundene Sprache zurückgeben
    return $current_lang;
  } // get()

} // class browserLanguage