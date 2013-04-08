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

    // get the PAGE_ID
    var PAGE_ID = $("[name='page_id']").val();
    var CONTROL_URL = CMS_ADDON_URL+"/vendor/phpManufaktur/extendedWYSIWYG/Control";

    // this timer hides the messages ...
    var timer = $.timer(function() {
      // we need all section ids
      $.get(CONTROL_URL+"/controlSections.php?page_id="+PAGE_ID, function(sections) {
        // explode result to section_ids
        var section_ids = sections.split(',');
        // loop through the sections
        for (var i=0; i<section_ids.length; i++) {
          $('#wysiwyg_info_'+section_ids[i]).html('');
        }
      });
    });
    // ... after 10 seconds!
    timer.set({ time: 10000, autostart: true });

    // get all WYSIWYG SECTION_IDs for this page
    $.get(CONTROL_URL+"/controlSections.php?page_id="+PAGE_ID, function(sections) {

      if (sections.indexOf('[') >= 0) {
        // if the result contains a [ an error was occured!
        // stop the timer
        timer.stop();
        // stop the script
        return false;
      }

      // explode result to section_ids
      var section_ids = sections.split(',');

      // loop through the sections
      for (var i=0; i<section_ids.length; i++) {

        // EVENT HANDLER for TEASER ARCHIVE SELECTION
        $("#teaser_id_"+section_ids[i]).live("change", function(event) {
          var TEASER_ID = $(this).val();
          var prefix = '#teaser_id_';
          var SECTION_ID = $(this).attr('name').substr(prefix.length-1);

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
            $('#wysiwyg_info_'+SECTION_ID).html(result.message);
          });
        }); // TEASER ARCHIVE SELECTION


        // EVENT HANDLER for SECTION ARCHIVE SELECTION
      	$("#archive_id_"+section_ids[i]).live("change", function(event) {
      		var prefix = '#archive_id_';
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
            $('#wysiwyg_info_'+SECTION_ID).html(result.message);
          });
      	}); // SECTION ARCHIVE SELECTION

      	// EVENT HANDLER for SAVE SECTION
      	$('#save_'+section_ids[i]).live('click', function(event) {
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
          var ARCHIVE_ID = $('#archive_id_'+SECTION_ID).val();
          var editor_name = $('#editor_name_'+SECTION_ID).val();
          var email_text = '';
          var approval = 0;
          var email_send = 0;
          var editor_action = 'NONE';
          var editor_response = 'NONE';

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

          if ($('input#approval_'+SECTION_ID).length > 0) {
            approval = $('#approval_'+SECTION_ID).attr('checked') ? 1 : 0;
          }

          if ($('input#email_send_'+SECTION_ID).length > 0) {
            email_send = $('#email_send_'+SECTION_ID).attr('checked') ? 1 : 0;
          }

          if ($('textarea#email_text_'+SECTION_ID).length > 0) {
            // get the content from the email editor
            email_text = encodeURI(document.getElementById('email_text_'+SECTION_ID).value);
          }

          // get the editor action and response
          editor_action = $("[name='editor_action_"+SECTION_ID+"']").val();
          editor_response = $("[name='editor_response_"+SECTION_ID+"']:checked").val();

          // transmit the contents to the control and show the result
          $.post(CONTROL_URL+'/controlContent.php', { 'page_id':PAGE_ID, 'section_id':SECTION_ID,
            'section_content':section_content, 'check_page_settings':check_page_settings, 'page_title':page_title,
            'page_description':page_description, 'page_keywords':page_keywords, 'section_publish':section_publish,
            'check_teaser':check_teaser, 'teaser_content':teaser_content, 'teaser_publish':teaser_publish,
            'teaser_id':TEASER_ID, 'archive_id':ARCHIVE_ID, 'editor_name':editor_name, 'email_text':email_text,
            'approval':approval,'editor_action':editor_action,'editor_response':editor_response,'email_send':email_send
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
                  $("<option/>").val(result.section.archive_id).text(result.section.option).prependTo("#archive_id_"+SECTION_ID);
                  $("#archive_id_"+SECTION_ID+" option[value='"+result.section.archive_id+"']").attr('selected', true);
                }
                if (result.section.status == 'RELOAD') {
                  // the page content must be reloaded
                  location.reload();
                }
              } // result.status == OK
              else {
                // ERROR - stop the timer!
                timer.stop();
              }
              $('#wysiwyg_info_'+SECTION_ID).html(result.message);

              if (email_send == 1) {
                // disable the email checkbox, reset the email text and hide the section
                $('#email_send_'+SECTION_ID).attr('checked', false);
                $('#email_container_'+SECTION_ID).html('');
                $('#email_container_'+SECTION_ID).css('display', 'none');
              }
          });

      	}); // EVENT HANDLER for SAVE SECTION

        // REQUEST APPROVAL
        $('#approval_'+section_ids[i]).live("click", function(event) {
          var prefix = '#approval_';
          var id = $(this).attr('name').substr(prefix.length-1);
          var SECTION_ID = $(this).attr('id').substr(prefix.length-1);
          var editor_name = $('#editor_name_'+SECTION_ID).val();

          if ($('#approval_'+id).attr('checked')) {
            // disable the email checkbox!
            $('#email_send_'+id).attr('checked', false);
            $.get(CONTROL_URL+'/controlEMailDialog.php?page_id='+PAGE_ID+'&section_id='+SECTION_ID+'&editor_name='+editor_name+'&email_type=approval',
                function(msg) {
              $('#email_container_'+id).html(msg);
              $('#email_container_'+id).css('display', 'block');
            });
          }
          else {
            $('#email_container_'+id).css('display', 'none');
          }
        });

        // SEND AN EMAIL TO THE DEPARTMENT
        $('#email_send_'+section_ids[i]).live("click", function(event) {
          var prefix = '#email_send_';
          var id = $(this).attr('name').substr(prefix.length-1);
          var SECTION_ID = $(this).attr('id').substr(prefix.length-1);
          var editor_name = $('#editor_name_'+SECTION_ID).val();

          if ($('#email_send_'+id).attr('checked')) {
            // disable the email checkbox!
            $('#approval_'+id).attr('checked', false);
            $('#email_container_'+id).css('display', 'none');
            $.get(CONTROL_URL+'/controlEMailDialog.php?page_id='+PAGE_ID+'&section_id='+SECTION_ID+'&editor_name='+editor_name+'&email_type=email',
                function(msg) {
              $('#email_container_'+id).html(msg);
              $('#email_container_'+id).css('display', 'block');
            });
          }
          else {
            $('#email_container_'+id).css('display', 'none');
          }
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