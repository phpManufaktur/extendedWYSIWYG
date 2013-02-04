<?php
/**
 *
 * @category        backend
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2010, Website Baker Org. e.V.
 * @link			http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 4.3.4 and higher
 * @version         $Id: save.php 1281 2010-01-30 04:57:58Z Luisehahne $
 * @filesource		$HeadURL: http://svn.websitebaker2.org/tags/2.8.1/wb/modules/wysiwyg/save.php $
 * @lastmodified    $Date: 2010-01-30 05:57:58 +0100 (Sa, 30. Jan 2010) $
 *
*/

require('../../config.php');

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Update the mod_wysiwygs table with the contents
if(isset($_POST['content'.$section_id])) {
	$content = $admin->add_slashes($_POST['content'.$section_id]);
	// searching in $text will be much easier this way
	$text = umlauts_to_entities(strip_tags($content), strtoupper(DEFAULT_CHARSET), 0);
	$database = new database();
	$query = "UPDATE ".TABLE_PREFIX."mod_wysiwyg SET content = '$content', text = '$text' WHERE section_id = '$section_id'";
	$database->query($query);	
}

if(defined('EDIT_ONE_SECTION') and EDIT_ONE_SECTION)
{
    $edit_page = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'&wysiwyg='.$section_id;
}
else
{
    $edit_page = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#wb'.$section_id;
}

// Check if there is a database error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), $js_back);
} else {
	$admin->print_success($MESSAGE['PAGES']['SAVED'], $edit_page );
}

// Print admin footer
$admin->print_footer();

?>