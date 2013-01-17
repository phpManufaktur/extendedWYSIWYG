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
