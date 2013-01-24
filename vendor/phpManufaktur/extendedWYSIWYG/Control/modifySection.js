/**
 * extendedWYSIWYG
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

if (typeof 'jQuery' != 'undefined') {
	
  $(document).ready(function() {
	  
    // this timer hides the messages ...
    var timer = $.timer(function() {
      $('#wysiwyg_info').css('display','none');
    });
    // ... afer 10 seconds!
    timer.set({ time: 10000, autostart: true });

    // get the PAGE_ID
    var PAGE_ID = $("[name='page_id']").val();
    var CONTROL_URL = CMS_ADDON_URL+"/vendor/phpManufaktur/extendedWYSIWYG/Control";
      
    // get all WYSIWYG SECTION_IDs for this page
    $.get(CONTROL_URL+"/controlSections.php?page_id="+PAGE_ID, function(sections) {

      if (sections.indexOf('[') >= 0) {
        // if the result contains a [ an error was occured!
        $('#wysiwyg_info').html(sections);
        // show the error message
        $('#wysiwyg_info').css('display', 'block');
        // stop the timer
        timer.stop();
        // stop the script
        return false;
      }
     
      // explode result to section_ids
      var section_ids = sections.split(',');

      // EVENT HANDLER for TEASER ARCHIVE SELECTION
      $("#teaser_id").live("change", function(event) {
        var TEASER_ID = $(this).val();
        
        $.get(CONTROL_URL+'/controlTeaserArchive.php', { 'page_id':PAGE_ID, 'teaser_id':TEASER_ID }, 
            function(msg) {
            
          var result = jQuery.parseJSON(msg);
          var instance_name = 'teaser_text';
          var publish_field = 'teaser_publish';
          var publish_status = (result.publish == 1);
          var section_content = result.content;
          
          if (result.status == 'OK') {
            // success - change the content of the editor
            for (var i in CKEDITOR.instances) {
              if (CKEDITOR.instances[i].name == instance_name) {
                // get the content from the CKE
                CKEDITOR.instances[i].setData(section_content, function() {
                  // Checks whether the current editor contents contain changes
                  this.checkDirty()
                });
              }
            }
            if (result.publish !== undefined) {
              $('#teaser_publish').prop('checked', publish_status);
            } 
          }                       
          $('#wysiwyg_info').html(result.message);
          $('#wysiwyg_info').css('display', 'block');
          var new_position = $('#wysiwyg_info').offset();
          window.scrollTo(new_position.left,new_position.top);
        });
      }); // TEASER ARCHIVE SELECTION
      
      
      // loop through the sections
      for (var i=0; i<section_ids.length; i++) {
        
        // EVENT HANDLER for SECTION ARCHIVE SELECTION
      	$("#archiv_id"+section_ids[i]).live("change", function(event) {
      		var prefix = '#archiv_id';
          var SECTION_ID = $(this).attr('name').substr(prefix.length-1);	
          var ARCHIVE_ID = $(this).val();
          
      		$.get(CONTROL_URL+'/controlWysiwygArchive.php', { 'page_id':PAGE_ID, 'section_id':SECTION_ID,
            'archive_id':ARCHIVE_ID }, function(msg) {
            try {
              var result = jQuery.parseJSON(msg);
            } catch (err) {
              alert("Error parsing the JSON result: "+msg);
            }
            var instance_name = 'content_'+SECTION_ID;
            var publish_field = '#publish_' + SECTION_ID;
            var publish_status = (result.publish == 1);
            var section_content = result.content;
            
            if (result.status == 'OK') {
              // success - change the content of the editor
              for (var i in CKEDITOR.instances) {
                if (CKEDITOR.instances[i].name == instance_name) {
                  // get the content from the CKE
                  CKEDITOR.instances[i].setData(section_content, function() {
                    // Checks whether the current editor contents contain changes
                    this.checkDirty()
                  });
                }
              }
              if (result.publish !== undefined) {
                $('#publish_'+SECTION_ID).prop('checked', publish_status);
              } 
            }                       
            $('#wysiwyg_info').html(result.message);
            $('#wysiwyg_info').css('display', 'block');
            var new_position = $('#wysiwyg_info').offset();
            window.scrollTo(new_position.left,new_position.top);
          });
      	}); // SECTION ARCHIVE SELECTION
      	
      	// EVENT HANDLER for SAVE SECTION
      	$('#save_'+section_ids[i]).live('click', function(event, section_id) {
      	  var prefix = '#save_';
          var SECTION_ID = $(this).attr('id').substr(prefix.length-1); 
          var instance_name = 'content_'+SECTION_ID;
          var section_content = '';
          var section_publish = $('#publish_'+SECTION_ID).attr('checked') ? 1 : 0;
          var page_title = '';
          var page_description = '';
          var page_keywords = '';
          var check_page_settings = $('#page_settings_'+SECTION_ID).attr('checked') ? 1 : 0;
          var check_teaser = $('#use_as_blog_'+SECTION_ID).attr('checked') ? 1 : 0;
          var TEASER_ID = -1;
          var teaser_content = '';
          var teaser_publish = 0;
          var ARCHIVE_ID = $('#archiv_id'+SECTION_ID).val();
          
          // loop through the CKEDITOR instances
          for (var i in CKEDITOR.instances) {
            // get the content from the CKE for this section
            if (CKEDITOR.instances[i].name == instance_name) {
              section_content = encodeURI(CKEDITOR.instances[i].getData());
            }
            if (check_teaser == 1) {
              // get the teaser content from the CKE
              if (CKEDITOR.instances[i].name == 'teaser_text') {
                teaser_content = encodeURI(CKEDITOR.instances[i].getData());
                // get the teaser content and settings
                teaser_publish = $('#teaser_publish').attr('checked') ? 1 : 0;
                TEASER_ID = $('#teaser_id').val();
              }            
            }
          } 
          
          if (check_page_settings == 1) {
            // get the page settings
            page_title = encodeURI(document.getElementById('page_title').value);
            page_description = encodeURI(document.getElementById('page_description').value);
            page_keywords = encodeURI(document.getElementById('page_keywords').value);
          }     
          
          // transmit the contents to the control and show the result
          $.get(CONTROL_URL+'/controlContent.php', { 'page_id':PAGE_ID, 'section_id':SECTION_ID,
            'section_content':section_content, 'check_page_settings':check_page_settings, 'page_title':page_title,
            'page_description':page_description, 'page_keywords':page_keywords, 'section_publish':section_publish,
            'check_teaser':check_teaser, 'teaser_content':teaser_content, 'teaser_publish':teaser_publish,
            'teaser_id':TEASER_ID, 'archive_id':ARCHIVE_ID
            }, function(msg) {
              // check the result
              try {
                var result = jQuery.parseJSON(msg);
              }
              catch (err) {
                alert("Error parsing the JSON result: "+msg);
              }
              if (result.status == 'OK') {              
                if (result.teaser.status == 'CHANGED') {
                  $("<option/>").val(result.teaser.teaser_id).text(result.teaser.option).prependTo("#teaser_id");
                  $("#teaser_id option[value='"+result.teaser.teaser_id+"']").attr('selected', true);
                }
                if (result.section.status == 'CHANGED') {
                  $("<option/>").val(result.section.archive_id).text(result.section.option).prependTo("#archiv_id"+SECTION_ID);
                  $("#archiv_id"+SECTION_ID+" option[value='"+result.section.archive_id+"']").attr('selected', true);
                }
              } // result.status == OK
              else {
                // ERROR - stop the timer!
                timer.stop();
              }
              $('#wysiwyg_info').html(result.message);
              $('#wysiwyg_info').css('display', 'block');
              var new_position = $('#wysiwyg_info').offset();
              window.scrollTo(new_position.left,new_position.top); 
          });
      	
      	});
    	  
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
          $.get(CONTROL_URL+"/controlOptions.php?page_id="+PAGE_ID+"&section_id="+id+"&options="+options_str, function(msg) {
            if (msg != 'OK') {
              $('#wysiwyg_info').html(msg);
              $('#wysiwyg_info').css('display', 'block');
            }
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
            $.get(CONTROL_URL+"/controlOptions.php?page_id="+PAGE_ID+"&section_id="+id+"&options="+options_str, function(msg) {
              if (msg != 'OK') {
                $('#wysiwyg_info').html(msg);
                $('#wysiwyg_info').css('display', 'block');
              }
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
              $.get(CONTROL_URL+"/controlOptions.php?page_id="+PAGE_ID+"&section_id="+id+"&options="+options_str, function(msg) {
                if (msg != 'OK') {
                  $('#wysiwyg_info').html(msg);
                  $('#wysiwyg_info').css('display', 'block');
                }
              });
          });
        }
      }
    });
  });
}