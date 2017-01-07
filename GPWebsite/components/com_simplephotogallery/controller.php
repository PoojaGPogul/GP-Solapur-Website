<?php
/**
 * Controller file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla controller library
jimport('joomla.application.component.controller');

/** 
 * Component Main Controller
 * 
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryControllersimplephotogallery extends SimplePhotoGalleryController {

   	/**
	 * Function to set layout and model for view page.
	 * 
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  An array of safe url parameters and their variable types
	 * 
	 * @return  simplephotogalleryController		This object to support chaining.
	 * 
	 * @since   1.5
	 */ 
    function display($cachable = false, $urlparams = false) {        
        parent::display();
    }
    
    /**
     * Function to download particualr image
     *
     * @return  publish
     */
    function download()
    {    	
    	$baseURL 	= JURI::base();
    	$db 		= JFactory::getDBO();
    	$query 		= $db->getQuery(true);
    	
    	$albumID 	= JRequest::getInt('albumid');
    	$photoID 	= JRequest::getInt('photoid');
    	
    	$query->clear()
		    	->select('image')
		    	->from('#__simplephotogallery_images')
		    	->where($db->quoteName('album_id') . ' = '. $db->quote($albumID))
		    	->where($db->quoteName('sortorder') . ' = '. $db->quote($photoID));

        $db->setQuery($query);
        $rows 		= $db->loadRow();
    	
		$file 		= $baseURL . 'images/photogallery/'.str_replace(' ','%20',trim($rows[0]));
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=" . basename($file));
		
		header("Content-Description: File Transfer");
		readfile($file);
		jexit();
    }
}
?>