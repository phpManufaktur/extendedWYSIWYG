<?php

/**
 * Markdown
 *
 * @author Michel Fortin
 * @link http://daringfireball.net
 * @copyright 2004-2013 Michel Fortin <http://michelf.ca/>
 * @license see Library/License.text
 */

namespace Markdown;

require_once __DIR__.'/Library/markdown.php';

class Markdown {

  /**
   * Parse Markdown text and return it HTML formatted
   *
   * @param string $content
   * @return string
   */
  public function parse($content) {
    return Markdown($content);
  } // parse()

} // class Markdown