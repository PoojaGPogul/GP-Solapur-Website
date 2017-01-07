<?php
/**
 * Album table file for Simple Photo Gallery
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
 * Album Administrator Table
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class Tablealbum extends JTable {
	public $id = null;
	public $album_name = null;
	public $alias_name = null;
	public $description = null;
	public $published = null;
	public $created_date = null;
	public $modified_date = null;

	/**
     * Function to get album table fields
     *
     * @param   object  &$db album table fields reference
     *
     * @return  void
     */
	function Tablealbum(&$db) {
		parent::__construct ( '#__simplephotogallery_album', 'id', $db );
	}
}

