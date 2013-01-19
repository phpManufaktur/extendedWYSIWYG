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

function saveSection(script_url, section_id, page_id) {
  var instance_name = 'content_'+section_id;
  var content;
  
  for (var i in CKEDITOR.instances) {
    if (CKEDITOR.instances[i].name == instance_name) {
      // get the content from the CKE
      content = encodeURI(CKEDITOR.instances[i].getData());
    }
  }
  
  var check_page_settings = 0;
  var page_title = '';
  var page_description = '';
  var page_keywords = '';
  if (document.getElementById('page_settings_'+section_id) && 
    (document.getElementById('page_settings_'+section_id).value == 1)) {
    check_page_settings = 1;
    page_title = encodeURI(document.getElementById('page_title').value);
    page_description = encodeURI(document.getElementById('page_description').value);
    page_keywords = encodeURI(document.getElementById('page_keywords').value);
  }
  alert('D: '+page_description+page_keywords);
  $.post(script_url, { 'section_id': section_id, 'section_content': content, 'page_id': page_id, 
    'check_page_settings': check_page_settings, 'page_title': page_title, 'page_description': page_description,
    'page_keywords': page_keywords },
  function(data) {
    alert("Data Loaded: " + data);
  });
  return false;
}
