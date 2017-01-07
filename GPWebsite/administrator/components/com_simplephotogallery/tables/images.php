<?php
/**
 * Images table file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct acesss
defined ( '_JEXEC' ) or die ( 'restricted access' );

/**
 * Images Administrator Table
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class Tableimages extends JTable {
	var $id = null;
	var $title = null;
	var $image = null;
	var $alias_name = null;
	var $description = null;
	var $album_id = null;
	var $sortorder = null;
	var $is_featured = null;
	var $published = null;
	var $album_cover = null;
	var $created_date = null;
	var $modified_date = null;
	
	/**
     * Function to get Images table fields
     *
     * @param   object  &$db Images table fields reference
     *
     * @return  void
     */
	function Tableimages(&$db) {
		parent::__construct ( '#__simplephotogallery_images', 'id', $db );
	}
}
