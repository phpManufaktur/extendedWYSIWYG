<?php

/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\extendedWYSIWYG\View;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\extendedWYSIWYG\View\viewException;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

global $I18n;

class modifySection extends boneClass {

  protected static $TEMPLATE_PATH = null;
  protected $lang = null;

  public function __construct() {
    global $I18n;

    $this->setInfo('Initialize Class View\modifySection', __METHOD__, __LINE__);
    self::$TEMPLATE_PATH = __DIR__.'/Templates/Backend/';
    $this->lang = $I18n;
  } // __construct()

  /**
   * Get the template, set the data and return the compiled
   *
   * @param string $template the name of the template
   * @param array $template_data
   * @param boolean $trigger_error raise a trigger error on problems
   * @return boolean|Ambigous <string, mixed>
   */
  protected function getTemplate($template, $template_data) {
    global $dwoo;

    // check if a custom template exists ...
    $load_template = (file_exists(self::$TEMPLATE_PATH.'custom.'.$template)) ? self::TEMPLATE_PATH.'custom.'.$template : self::$TEMPLATE_PATH.$template;
    try {
      $result = $dwoo->get('x'.$load_template, $template_data);
    }
    catch (\Dwoo_Exception $e) {
      $this->setError($this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()

  public function view() {

    $data = array(
        'is_error' => (int) $this->isError(),
        'content' => 'TEST'
        );
    return $this->getTemplate('body.dwoo', $data);
  } // view()

} // class modifySection