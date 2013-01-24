<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\kitCommand;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\kitCommand\Command;

class kitCommand extends boneClass {

  /**
   * Execute the main function for kitCommand
   *
   * @param string $content
   * @return string
   */
  public function Exec($content) {
    // search for kitCommands ~~ command ARG1[value, value] arg2[] ~~
    preg_match_all('/(~~ ).*( ~~)/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $command_expression = $match[0];
      // get the expression without leading and trailing ~~
      $command_string = trim(str_replace('~~', '', $match[0]));
      if (empty($command_string)) continue;
      // explode the string into an array by spaces
      $command_array = explode(' ', $command_string);
      // the first match is the command!
      $command = strtolower(trim($command_array[0]));
      // exists the command execution file?
      $command_path = CMS_ADDON_PATH."/vendor/phpManufaktur/kitCommand/Command/$command/$command.php";
      if (file_exists($command_path)) {
        // require the $command_path
        require_once $command_path;
        // the command is no longer needed
        unset($command_array[0]);
        $parameter_string = implode(' ', $command_array);
        $params = array();
        // now we search for the parameters
        preg_match_all('/([a-z,A-Z,0-9]{3,18}([ ]){0,1}\[)(.*?)(])/', $parameter_string, $parameter_matches, PREG_SET_ORDER);
        // loop through the parameters
        foreach ($parameter_matches as $parameter_match) {
          // the bracket [ separate key and value
          $parameter_pair = explode('[', $parameter_match[0]);
          // no pair? continue!
          if (count($parameter_pair) != 2) continue;
          // separate the key
          $key = strtolower(trim($parameter_pair[0]));
          // separate the value
          $value = trim(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']')));
          // add to the params array
          $params[$key] = $value;
        }
        $cmd = new $command;
        // execute the kitCommand
        $content = $cmd->exec($content, $command_expression, $command, $params);
      }
    }
    return $content;
  } // kitCommand()

} // class kitCommand