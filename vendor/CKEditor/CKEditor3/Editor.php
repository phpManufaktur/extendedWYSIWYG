<?php

/**
 * CKEditor v3
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace CKEditor\CKEditor3;

use CKEditor\CKEditor3\CKEditor;

class Editor extends CKEditor {

  public function exec($name, $content, $width='100%', $height='250px', $toolbar='default') {
    // set the basepath for the editor
    $this->basePath = CMS_ADDON_URL.'/vendor/CKEditor/CKEditor3/';
    // return the editor as HTML
    $this->returnOutput = true;
    $this->config['height'] = $height;
    $this->config['width'] = $width;

    // connect the filemanager
    $connectorPath = $this->basePath.'filemanager/connectors/php/connector.php';
    $this->config['filebrowserBrowseUrl'] = $this->basePath.'filemanager/browser/default/browser.html?Connector='.$connectorPath;
    $this->config['filebrowserImageBrowseUrl'] = $this->basePath.'filemanager/browser/default/browser.html?Type=Image&Connector='.$connectorPath;
    $this->config['filebrowserFlashBrowseUrl'] = $this->basePath.'filemanager/browser/default/browser.html?Type=Flash&Connector='.$connectorPath;

    // set the upload path's
    $uploadPath = $this->basePath.'filemanager/connectors/php/upload.php?Type=';
    $this->config['filebrowserUploadUrl'] = $uploadPath.'File';
    $this->config['filebrowserImageUploadUrl'] = $uploadPath.'Image';
    $this->config['filebrowserFlashUploadUrl'] = $uploadPath.'Flash';

    $this->config['language'] = strtolower(CMS_LANGUAGE);
    $this->config['toolbar'] = ($toolbar == 'default') ? 'Smart' : $toolbar;

    return $this->editor($name, $content);
  } //

} // class wysiwygEditor