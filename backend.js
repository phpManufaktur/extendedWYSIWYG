/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

function execOnChange(target_url, select_id, anchor) {
  var x;
  x = target_url + document.getElementById(select_id).value + '#' + anchor;
  document.body.style.cursor='wait';
  window.location = x;
  return false;	
}

function saveSection(sec_id) {
  var instance_name = 'content_'+sec_id;
  var content;
  
  for (var i in CKEDITOR.instances) {
    if (CKEDITOR.instances[i].name == instance_name) {
      // get the content from the CKE
      content = encodeURI(CKEDITOR.instances[i].getData());
    }
  }
  $.post('http://test.dev.phpmanufaktur.de/modules/wysiwyg/vendor/phpManufaktur/extendedWYSIWYG/Control/getCKEcontent.php', { section_id: sec_id, 'section_content': content },
  function(data) {
    alert("Data Loaded: " + data);
  });
  return false;
}
