<?php
/**
 * Simplephotogallery View file
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

// Import joomla view library
jimport('joomla.application.component.view');

/**
 * Simplephotogallery View class
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryViewsimplephotogallery extends SimplePhotoGalleryView 
{

	/**
	 * Function to set layout and model for view page.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  An array of safe url parameters and their variable types
	 *
	 * @return  SimplephotogalleryViewsimplephotogallery	This object to support chaining.
	 *
	 * @since   1.5
	 */
    function display($tpl = NULL) 
    {	
    	$model 	= $this->getModel('simplephotogallery');

    	$images = $model->getImages();
    	$this->assignRef('images', $images);
    	
    	$settings = $model->getSettings();
    	$this->assignRef('settings', $settings);
    	
    	$album = $model->getAlbum();
    	$this->assignRef('album', $album);    	
    	
    	$totalAlbum = $model->getTotalAlbum();
    	$this->assignRef('totalAlbum', $album);
    	
    	parent::display();
    }
}