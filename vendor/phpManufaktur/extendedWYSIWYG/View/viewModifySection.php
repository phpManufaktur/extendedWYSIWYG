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

use phpManufaktur\extendedWYSIWYG\Control\boneModifySection;

require_once CMS_PATH.'/modules/dwoo/dwoo-1.1.1/dwoo/Dwoo/Exception.php';

class viewModifySection extends boneModifySection {

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
      $result = $dwoo->get($load_template, $template_data);
    }
    catch (\Dwoo_Exception $e) {
      $this->setError($this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}',
          array('template' => basename($load_template), 'error' => $e->getMessage())), __METHOD__, $e->getLine());
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Return the completed View
   *
   * @return Ambigous <boolean, \phpManufaktur\extendedWYSIWYG\View\Ambigous, string, mixed>
   */
  protected function show($content) {
    $data = array(
        'section_position' => self::$SECTION_POSITION,
        'section_is_first' => self::$SECTION_IS_FIRST,
        'CMS_ADDON_URL' => CMS_ADDON_URL,
        'anchor' => self::$SECTION_ANCHOR,
        'is_error' => (int) $this->isError(),
        'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.dwoo', $data);
  } // show()

  public function showError($error) {
    $this->setError($error, __METHOD__, __LINE__);
    return $this->show('');
  }

  public function promptError($error) {
    return $this->getTemplate('error.dwoo', array('content' => $error));
  } // dialogError()

  public function dialogApprovalEMail($data) {
    return $this->getTemplate('modify.email.approval.dwoo', $data);
  } // dialogApprovalEMail()

  public function dialogEMail($data) {
    return $this->getTemplate('modify.email.dwoo', $data);
  } // dialogEMail()

  public function dialogModify($data) {
    $dialog = $this->getTemplate('modify.dwoo', $data);
    return $this->show($dialog);
  } // dialogModify()

  public function AccessDenied() {
    $dialog = $this->getTemplate('access.denied.dwoo', array());
    return $this->show($dialog);
  } // AccessDenied

} // class modifySection