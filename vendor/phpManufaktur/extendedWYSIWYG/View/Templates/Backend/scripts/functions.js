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

if (typeof 'jQuery' != 'undefined') {
  $(document).ready(function() {
    // get the PAGE_ID
    var page_id = $("[name='page_id']").val();

    // get all WYSIWYG SECTION_IDs for this page
    $.get(CMS_ADDON_URL+"/presets/extendedWYSIWYG/getSections.php?page_id="+page_id, function(sections) {

      // explode result to section_ids
      var section_ids = sections.split(',');

      // loop through the sections
      for (var i=0; i<section_ids.length; i++) {

        // preset the visibility depending on the options checkboxes
        if ($('#hide_section_'+section_ids[i]).attr('checked'))
          $("#wysiwyg_container_"+section_ids[i]).css('display','none');
        else
          $("#wysiwyg_container_"+section_ids[i]).css('display','block');

        // checking live all changes at the options checkboxes
        $('#hide_section_'+section_ids[i]).live("click", function(event) {
          var prefix = '#hide_section_';
          var id = $(this).attr('name').substr(prefix.length-1);
          var options = [];
          if ($('#hide_section_'+id).attr('checked')) {
            $('#wysiwyg_container_'+id).css('display','none');
            options.push('hide_section');
          }
          else
            $('#wysiwyg_container_'+id).css('display','block');

          if ($('#page_settings_'+id).attr('checked'))
            options.push('page_settings');
          if ($('#use_as_blog_'+id).attr('checked'))
            options.push('use_as_blog');
          var options_str = options.join();
          $.get("http://test.dev.phpmanufaktur.de/modules/wysiwyg/presets/extendedWYSIWYG/setOptions.php?page_id="+page_id+"&section_id="+id+"&options="+options_str, function(msg) {
           if (msg != 'OK') alert(msg);
          });
        });

        // we use the page settings only at the first section!
        if (i == 0) {
          // preset the visibility depending on the options checkboxes
          if ($('#page_settings_'+section_ids[i]).attr('checked')) {
            $("#wysiwyg_page_settings").css('display','block');
          }
          else {
            $("#wysiwyg_page_settings").css('display','none');
          }
          // checking live all changes at the options checkboxes
          $('#page_settings_'+section_ids[i]).live("click", function(event) {
            var prefix = '#page_settings_';
            var id = $(this).attr('name').substr(prefix.length-1);
            var options = [];
            if ($('#page_settings_'+id).attr('checked')) {
              $('#wysiwyg_page_settings').css('display','block');
              options.push('page_settings');
            }
            else
              $('#wysiwyg_page_settings').css('display','none');
            if ($('#hide_section_'+id).attr('checked'))
              options.push('hide_section');
            if ($('#use_as_blog_'+id).attr('checked'))
              options.push('use_as_blog');
            var options_str = options.join();
            $.get("http://test.dev.phpmanufaktur.de/modules/wysiwyg/presets/extendedWYSIWYG/setOptions.php?page_id="+page_id+"&section_id="+id+"&options="+options_str, function(msg) {
              if (msg != 'OK') alert(msg);
            });
          });
        }

        // we use the feature "use as blog" only at the first section!
        if (i == 0) {
          // preset the visibility depending on the options checkboxes
          if ($('#use_as_blog_'+section_ids[i]).attr('checked')){
            $("#wysiwyg_use_as_blog").css('display','block');
          }
          else {
            $("#wysiwyg_use_as_blog").css('display','none');
          }
          // checking live all changes at the options checkboxes
          $('#use_as_blog_'+section_ids[i]).live("click", function(event) {
            var prefix = '#use_as_blog_';
            var id = $(this).attr('name').substr(prefix.length-1);
            var options = [];
            if ($('#use_as_blog_'+id).attr('checked')) {
              $('#wysiwyg_use_as_blog').css('display','block');
              options.push('use_as_blog');
            }
            else
              $('#wysiwyg_use_as_blog').css('display','none');
              if ($('#hide_section_'+id).attr('checked'))
                options.push('hide_section');
              if ($('#page_settings_'+id).attr('checked'))
                options.push('page_settings');
              var options_str = options.join();
              $.get("http://test.dev.phpmanufaktur.de/modules/wysiwyg/presets/extendedWYSIWYG/setOptions.php?page_id="+page_id+"&section_id="+id+"&options="+options_str, function(msg) {
                if (msg != 'OK') alert(msg);
              });
          });
        }
      }
    });
  });
}