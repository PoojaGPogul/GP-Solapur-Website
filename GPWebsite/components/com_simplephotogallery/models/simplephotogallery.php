<?php
/**
 * Simplephotogallery model file to display featured images and album list
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

class SimplephotogalleryModelsimplephotogallery extends SimplePhotoGalleryModel 
{
    /**
	 * This function is to get album images from images table.
	 *
	 * @return	objectarray 	$rows 		Imagelist 
	 */
	function getImages()
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select('*')
				->from('#__simplephotogallery_settings');
        $db->setQuery($query);
        
        $settings 		= $db->loadObjectList();
       	$rowCount 		= $settings[0]->feat_rows;
       	$columnCount 	= $settings[0]->feat_cols;
       	$totalCount 	= $rowCount * $columnCount;

       	$query->clear()
       			->select(array('img.*','alb.album_name'))
		       	->from('#__simplephotogallery_images AS img')
		       	->innerJoin('#__simplephotogallery_album AS alb ON alb.id = img.album_id')
		       	->where($db->quoteName('img.is_featured') . ' = ' . $db->quote('1'))
		       	->where($db->quoteName('img.published') . ' = ' . $db->quote('1'))
		       	->order($db->escape('RAND()'));
        $db->setQuery($query, 0, $totalCount);
        
        $rows 	= $db->loadObjectList();        
        return $rows;
	}
	
	/**
	 * This function is to get settings data from settings table.
	 *
	 * @return	objectlist 	$settings 		Settingslist
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
	 * This function is to get published albums data from settings table.
	 *
	 * @return	objectlist 	$settings 		Settingslist
	 */
	function getTotalAlbum()
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select('*')
				->from('#__simplephotogallery_album')
				->where($db->quotename('published') . ' = ' . $db->quote('1'));
        $db->setQuery($query);
        $settings 	= $db->loadRow();
        return $settings;
	}
	
	/**
	 * This function is to get album,image data from album,images table.
	 *
	 * @return	objectlist 	$settings 		album
	 */
	function getAlbum()
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$query->clear()
				->select(array('DISTINCT (a.id)','a.album_name','a.description','b.album_id','b.album_cover','b.image'))
				->from('#__simplephotogallery_album AS a')
				->leftJoin('#__simplephotogallery_images AS b ON b.album_id = a.id AND b.album_cover = 1')
				->where($db->quotename('a.published') . ' = ' . $db->quote('1'))
				->group($db->escape('a.id'))
				->order($db->escape('a.id' . ' ' . 'DESC'));		
		$db->setQuery($query);
        $album 	= $db->loadObjectList();
        return $album;
	}	
}