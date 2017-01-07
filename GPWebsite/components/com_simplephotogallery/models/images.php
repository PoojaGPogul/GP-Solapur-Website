<?php
/**
 * Images model file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct acesss
defined('_JEXEC') or die();

// Import joomla model library
jimport('joomla.application.component.model');

/**
 * Images Component Model
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryModelimages extends SimplePhotoGalleryModel 
{
    /**
	 * This function is to get images list from images table.
	 *
	 * @param   int  			$albumID  	album id
	 * @param   int  			$photoID  	photo id
	 * 
	 * @return	objectarray 	$rows 		imageslist 
	 */	 
	function getImages($albumID, $photoID)
	{	
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);

		if(empty($albumID))
		{
			$query->clear()
					->select('id')
					->from('#__simplephotogallery_album')
					->where($db->quoteName('published') . ' = '. $db->quote('1'))
					->order($db->escape('id' . ' ' . 'ASC'));			
			$db->setQuery($query, 0, 1);
        	$rows = $db->loadRow();
        	
			$query->clear()
					->select('*')
					->from('#__simplephotogallery_images')
					->where($db->quoteName('published') . ' = '. $db->quote('1'))
					->where($db->quoteName('album_id') . ' = '. $db->quote($rows[0]));
			$db->setQuery($query);
		}
		else 
		{	
			$query->clear()
			->select('*')
			->from('#__simplephotogallery_images')			
			->where($db->quoteName('published') . ' = '. $db->quote('1'))
			->where($db->quoteName('album_id') . ' = '. $db->quote($albumID));
			if($photoID != '' && !JRequest::getVar('order') )
			{
				$query->where($db->quoteName('sortorder') . ' = '. $db->quote($photoID));
				$db->setQuery($query,0,1);
			}
			else
			{
				$db->setQuery($query);
			}
				
			
		}        
        $rows = $db->loadObjectList(); 
        return $rows; 
	}
	
	/**
	 * This function is to count total images for the given album id.
	 *
	 * @params	int				$albumID	album id
	 * 
	 * @return	object 			$rows 		total
	 */
	function totalImage($albumID)
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select('count(*) AS total')
				->from('#__simplephotogallery_images')
				->where($db->quoteName('published') . ' = '. $db->quote('1'))
				->where($db->quoteName('album_id') . ' = '. $db->quote($albumID));
        $db->setQuery($query);
        
        $rows = $db->loadRow();
        return $rows;
	}
	
	/**
	 * This function is to get album details for the given album id.
	 *
	 * @params	int				$albumID	album id
	 *
	 * @return	object 			$rows 		album details
	 */
	function getAlbumName($albumID)
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		if(empty($albumID))
		{
			$query->clear()
					->select(array('album_name','description','id'))
					->from('#__simplephotogallery_album')
					->where($db->quoteName('published') . ' = '. $db->quote('1'))
					->order($db->escape('id' . ' ' . 'ASC'));
			$db->setQuery($query,0,1);
		}
		else 
		{
			$query->clear()
					->select(array('album_name','description','id'))
					->from('#__simplephotogallery_album')
					->where($db->quoteName('id') . ' = '. $db->quote($albumID));        	
        	$db->setQuery($query);
		}
        
        $settings = $db->loadRow();
        return $settings;
	}
	
	/**
	 * This function is to get settings from settings table.	 
	 *
	 * @return	objectlist 		$rows 		settings detail
	 */
	function getSettings()
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select('*')
				->from('#__simplephotogallery_settings');
        $db->setQuery($query);
        $settings 	= $db->loadObjectList();
        return $settings;
	}
	
	/**
	 * This function is to get settings from settings table.
	 *
	 * @return	objectlist 		$rows 		settings detail
	 */
	function getAlbum()
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select(array('DISTINCT (a.id)','a.album_name','a.description','b.album_id','b.album_cover','b.image'))
				->from('#__simplephotogallery_album AS a')
				->leftJoin('#__simplephotogallery_images AS b ON b.album_id = a.id AND b.album_cover = 1')
		 		->where($db->quoteName('a.published') . ' = ' . $db->quote('1')) 
		 		->group($db->escape('a.id'))
		 		->order($db->escape('a.id' . ' ' . 'DESC'));
		$db->setQuery($query);
        $album = $db->loadObjectList();
        return $album;
	}
}