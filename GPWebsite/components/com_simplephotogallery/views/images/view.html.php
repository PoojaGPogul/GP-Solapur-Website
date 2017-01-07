<?php
/**
 * Images View file for Simple Photo Gallery
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
 * Images View class
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryViewimages extends SimplePhotoGalleryView 
{

	/**
	 * Function to set layout and model for view page.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  An array of safe url parameters and their variable types
	 *
	 * @return  SimplephotogalleryViewimages		This object to support chaining.
	 *
	 * @since   1.5
	 */
    function display($cachable = false, $urlparams = false) 
    {
    	$photoID = $albumID = '';
    	
    	$photoID = JRequest::getInt('photoid');    	
    	$albumID = JRequest::getInt('albumid');
    	
    	$model 	= $this->getModel('images');
    	$images = $model->getImages($albumID, $photoID);     	    	
    	$this->assignRef('images', $images);
    	
    	$settings 	= $model->getSettings();    	
    	$this->assignRef('settings', $settings);
    	
    	$album = $model->getAlbum();
    	$this->assignRef('album', $album);
    	
    	$albumName 		= $model->getAlbumName($albumID);
    	$totalPhotos 	= $model->totalImage($albumID);
		$totalPhotos 	= $totalPhotos[0];
		
    	$mainframe 		= JFactory::getApplication();
        $pathway   		= $mainframe->getPathway();
         
        if (JRequest::getCmd("view") == "images" && !JRequest::getVar("photoid")) 
        {
            $pathway->addItem(JText::_("Albums"), JRoute::_("index.php?option=com_simplephotogallery") );
            $pathway->addItem(ucfirst($albumName[0]), '');
            
        }
        else if (JRequest::getCmd("view") == "images" && JRequest::getVar("photoid")) 
        {
         	$pathway->addItem(ucfirst($albumName[0]), JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=".(int)JRequest::getVar('albumid')) );
            $pathway->addItem(ucfirst($images[0]->title), '');
            $pathway->addItem(JText::_('Photo').' '.(int)JRequest::getVar("photoid").' of '.$totalPhotos, '');
        }

    	parent::display();
    }
}