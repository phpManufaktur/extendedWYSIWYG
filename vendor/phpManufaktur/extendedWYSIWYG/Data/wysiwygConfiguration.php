<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\extendedWYSIWYG\Data;

use phpManufaktur\CMS\Bridge\Control\boneClass;

require_once CMS_PATH.'/modules/manufaktur_config/library.php';

class wysiwygConfiguration extends boneClass {

  protected $config = null;

  public function __construct() {
    $this->config = new \manufakturConfig();
  } // __construct()

  /**
   * Get a configuration value from the manufakturConfig database
   *
   * @param string $name
   * @return mixed
   */
  public function getValue($name) {
    if (null === ($value = $this->config->getValue($name, 'wysiwyg'))) {
      $this->setError($this->config->getError(), __METHOD__, __LINE__);
      return false;
    }
    return $value;
  } // getValue()

} // class wysiwygConfiguration