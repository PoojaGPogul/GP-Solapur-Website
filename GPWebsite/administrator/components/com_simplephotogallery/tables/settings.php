<?php
/**
 * Settings table file for Simple Photo Gallery
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
 * Settings Administrator Table
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class Tablesettings extends JTable {
	var $id = null;
	var $feat_cols = null;
	var $feat_rows = null;
	var $feat_photo_width = null;
	var $feat_photo_height = null;
	var $feat_vspace = null;
	var $feat_hspace = null;
	var $alb_photo_width = null;
	var $alb_photo_height = null;
	var $alb_vspace = null;
	var $alb_hspace = null;
	var $general_share_photo = null;
	var $general_download = null;
	var $general_show_alb = null;
	var $facebook_api = null;
	var $thumbimg_width = null;
	var $thumbimg_height = null;
	var $photo_vspace = null;
	var $photo_hspace = null;
	var $fullimg_width = null;
	var $fullimg_height = null;
	var $facebook_api_id = null;
	
	/**
     * Function to get Settings table fields
     *
     * @param   object  &$db Settings table fields reference
     *
     * @return  void
     */
	function Tablesettings(&$db) {
		parent::__construct ( '#__simplephotogallery_settings', 'id', $db );
	}
}

