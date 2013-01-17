<?php
/**
 *
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link			http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version      	$Id: install.php 1457 2011-06-25 17:18:50Z Luisehahne $
 * @filesource		$HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/modules/wysiwyg/install.php $
 * @lastmodified    $Date: 2011-06-25 19:18:50 +0200 (Sa, 25. Jun 2011) $
 *
 */

if(defined('WB_URL'))
{
	
	// Create table
	//$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wysiwyg`");
	$mod_wysiwyg = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_wysiwyg` ( '
		. ' `section_id` INT NOT NULL DEFAULT \'0\','
		. ' `page_id` INT NOT NULL DEFAULT \'0\','
		. ' `content` LONGTEXT NOT NULL ,'
		. ' `text` LONGTEXT NOT NULL ,'
		. ' PRIMARY KEY ( `section_id` ) '
		. ' )';
	$database->query($mod_wysiwyg);
	

    $mod_search = "SELECT * FROM ".TABLE_PREFIX."search  WHERE value = 'wysiwyg'";
    $insert_search = $database->query($mod_search);
    if( $insert_search->numRows() == 0 )
    {
    	// Insert info into the search table
    	// Module query info
    	$field_info = array();
    	$field_info['page_id'] = 'page_id';
    	$field_info['title'] = 'page_title';
    	$field_info['link'] = 'link';
    	$field_info['description'] = 'description';
    	$field_info['modified_when'] = 'modified_when';
    	$field_info['modified_by'] = 'modified_by';
    	$field_info = serialize($field_info);
    	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('module', 'wysiwyg', '$field_info')");
    	// Query start
    	$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title,	[TP]pages.link, [TP]pages.description, [TP]pages.modified_when, [TP]pages.modified_by	FROM [TP]mod_wysiwyg, [TP]pages WHERE ";
    	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_start', '$query_start_code', 'wysiwyg')");
    	// Query body
    	$query_body_code = " [TP]pages.page_id = [TP]mod_wysiwyg.page_id AND [TP]mod_wysiwyg.text [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'";
    	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_body', '$query_body_code', 'wysiwyg')");
    	// Query end
    	$query_end_code = "";
    	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_end', '$query_end_code', 'wysiwyg')");

    	// Insert blank row (there needs to be at least on row for the search to work)
    	$database->query("INSERT INTO ".TABLE_PREFIX."mod_wysiwyg (page_id,section_id) VALUES ('0','0')");


    }
}

?>