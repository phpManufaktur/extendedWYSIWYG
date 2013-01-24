<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */


class system {

  /**
   * Search and replace function
   *
   * @param string $content
   * @param string $command_expression
   * @param string $value
   * @return mixed
   */
  protected function replace($content, $command_expression, $value) {
    if (strtoupper($value) == 'CMS_MEDIA_URL') {
      // replace the placeholder with real URL of the media directory
      $content = str_replace($command_expression, CMS_MEDIA_URL, $content);
      return $content;
    }
    if (false !== strpos($value, ',')) {
      // search and replace the given terms
      $search_pair = explode(',', $value);
      if (count($search_pair) == 2) {
        $search = trim($search_pair[0]);
        $replace = trim($search_pair[1]);
        // remove the $command_expression
        $content = str_replace($command_expression, '', $content);
        // search and replace the pair
        $content = str_replace($search, $replace, $content);
        return $content;
      }
    }
    return $content;
  } // replace()

  protected function striptags($content, $command_expression, $value) {
    // remove the $command_expression
    $content = str_replace($command_expression, '', $content);
    $content = trim(strip_tags($content));
    return $content;
  } // striptags()

  /**
   * Execute the kitCommand "system"
   *
   * @param string $content
   * @param string $command_expression
   * @param string $command
   * @param string $params
   * @return string
   */
  public function exec($content, $command_expression, $command, $params) {

    foreach ($params as $parameter => $value) {
      $parameter = strtolower($parameter);
      switch ($parameter):
      case 'replace':
        $content = $this->replace($content, $command_expression, $value);
        break;
      case 'striptags':
        $content = $this->striptags($content, $command_expression, $value);
        break;
      endswitch;
    }
    return $content;
  } // system()
}
