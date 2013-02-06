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
 * @version         $Id: view.php 1457 2011-06-25 17:18:50Z Luisehahne $
 * @filesource		$HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/modules/wysiwyg/view.php $
 * @lastmodified    $Date: 2011-06-25 19:18:50 +0200 (Sa, 25. Jun 2011) $
 *
 */
// Must include code to stop this file being access directly
if(defined('WB_PATH') == false) { die("Cannot access this file directly"); }
// Get content
$get_content = $database->query("SELECT content FROM ".TABLE_PREFIX."mod_wysiwyg WHERE section_id = '$section_id'");
$fetch_content = $get_content->fetchRow();
$content = ($fetch_content['content']);

/**
 * FIX: restore extendedWYSIWYG data handling
 */
$content = stripcslashes($content);
$content = str_replace(array("&lt;","&gt;","&quot;","&#039;"), array("<",">","\"","'"), $content);
$content = str_replace('~~ wysiwyg replace[CMS_MEDIA_URL] ~~', WB_URL.MEDIA_DIRECTORY, $content);

echo $content;

?>