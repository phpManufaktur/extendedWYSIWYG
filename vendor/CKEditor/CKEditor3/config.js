/**
 * CKEditor v3
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

CKEDITOR.editorConfig = function(config) {
  
  config.templates_replaceContent =   true;
  // Define all extra CKEditor plugins in _yourwb_/modules/ckeditor/ckeditor/plugins here
  config.extraPlugins = 'dropleps,pagelink';
    
  // Different Toolbars. Remove, add or move 'SomeButton', with the quotes and following comma 
  config.toolbar_Full = [['Source','-','Preview','Templates'],['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker','Scayt'],['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],['Maximize','ShowBlocks','-','About'],'/',['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['dropleps','pagelink','Link','Unlink','Anchor'],['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar'],'/',['Styles','Format','Font','FontSize'],['TextColor','BGColor']];
  config.toolbar_Smart = [
      ['Source'],['Cut','Copy','Paste','PasteText','PasteFromWord'],['Image','Flash','Table'],['dropleps','pagelink','Link','Unlink','Anchor'],['Undo','Redo','-','SelectAll','RemoveFormat'],['Maximize','ShowBlocks','-','About'],'/',
      ['Format','FontSize'],['Bold','Italic','Underline','Strike'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv']
    ];
  config.toolbar_Simple = [['Bold','Italic','-','NumberedList','BulletedList','-','Image','-','dropleps','pagelink','Link','Unlink','-','Scayt','-','About']];
     
	// The default toolbar. Default: Full
  config.toolbar = 'Smart';
    
  // Explanation: _P: new <p> paragraphs are created; _BR: lines are broken with <br> elements;
  //              _DIV: new <div> blocks are created.
  // Sets the behavior for the ENTER key. Default is _P allowed tags: _P | _BR | _DIV
  config.enterMode = CKEDITOR.ENTER_P; 
  // Sets the behavior for the Shift + ENTER keys. allowed tags: _P | _BR | _DIV
  config.shiftEnterMode = CKEDITOR.ENTER_BR; 
    
  // The language to be used if config.language is empty and it's not possible to localize the editor to the user language.
  config.defaultLanguage  = 'en';
  config.docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    
  // the skin to be used
  config.skin = 'kama';
    
  // The standard height and width of CKEditor in pixels.
  config.height = '250';
  config.width = '900';
  config.toolbarLocation  = 'top';
    
  // Define possibilities of automatic resizing in pixels. Set config.resize_enabled to false to 
  // deactivate resizing.
  config.resize_enabled   = true;
  config.resize_minWidth  = 500;
  config.resize_maxWidth  = 1500;
  config.resize_minHeight = 200;
  config.resize_maxHeight = 1200;
    
  /* Protect PHP code tags (<?...?>) so CKEditor will not break them when switching from Source to WYSIWYG.
   *  Uncommenting this line doesn't mean the user will not be able to type PHP code in the source.
   *  This kind of prevention must be done in the server side, so just leave this line as is. */ 
  config.protectedSource.push(/<\?[\s\S]*?\?>/g); // PHP Code
  
}; // CKEDITOR.editorConfig


CKEDITOR.on('instanceReady', function(ev) {  
  var writer = ev.editor.dataProcessor.writer;
  
  // The character sequence to use for every indentation step.
  writer.indentationChars = '\t';
  // The way to close self closing tags, like <br />.
  writer.selfClosingEnd   = ' />';
  // The character sequence to be used for line breaks.
  writer.lineBreakChars   = '\n';
  // Setting rules for several HTML tags.
    
  var dtd = CKEDITOR.dtd;
  for (var e in CKEDITOR.tools.extend( {}, dtd.$block )) {
    writer.setRules(e, {
      // Indicates that this tag causes indentation on line breaks inside of it.
      indent : true,
      // Insert a line break before the <h1> tag.
      breakBeforeOpen : true,
      // Insert a line break after the <h1> tag.
      breakAfterOpen : false,
      // Insert a line break before the </h1> closing tag.
      breakBeforeClose : false,
      // Insert a line break after the </h1> closing tag.
      breakAfterClose : true
    });
  };
  writer.setRules('p',  {
    // Indicates that this tag causes indentation on line breaks inside of it.
    indent : true,
    // Insert a line break before the <p> tag.
    breakBeforeOpen : true,
    // Insert a line break after the <p> tag.
    breakAfterOpen : false,
    // Insert a line break before the </p> closing tag.
    breakBeforeClose : false,
    // Insert a line break after the </p> closing tag.
    breakAfterClose : true
  });
  writer.setRules('br', {
    // Indicates that this tag causes indentation on line breaks inside of it.
    indent : false,
    // Insert a line break before the <br /> tag.
    breakBeforeOpen : false,
    // Insert a line break after the <br /> tag.
    breakAfterOpen : false
  });
});
