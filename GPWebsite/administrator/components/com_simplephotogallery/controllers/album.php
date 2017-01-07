<?php
/**
 * Album controller file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import joomla controller library
jimport( 'joomla.application.component.controller' );

/** 
 * Album Administrator Controller
 * 
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryControlleralbum extends SimplePhotoGalleryController
{   
	/**
	 * Function to set layout and model for view page.
	 * 
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  An array of safe url parameters and their variable types
	 * 
	 * @return  SimplephotogalleryControlleralbum		This object to support chaining.
	 * 
	 * @since   1.5
	 */ 
    function display($cachable = false, $urlparams = false)
    {
    	parent::display();
    }
    
    /**
     * This function is to save new album and redirect to albumlist grid section.
     * 
     * @return  save
     */ 
	function save()
    {
        $album 	= JRequest::get('POST');    
        $model 	= $this->getModel('album');
        
        $model->saveAlbum($album);
        $this->setRedirect('index.php?view=album&option='.JRequest::getVar('option'), 'Saved!');
    }
    
    /**
     * Function to publish the item from the listing.
     * 
     * @return  publish
     */
	function publish()
    {
        $album 	= JRequest::get('POST');
        $model 	= $this->getModel('album');
        
        $model->publish($album);
        $this->setRedirect('index.php?view=album&option='.JRequest::getVar('option'));
    }

    /**
     * Function to unpublish the item from the listing.
     * 
     * @return  unpublish
     */ 
    function unpublish()
    {
        $album 	= JRequest::get('POST');
        $model 	= $this->getModel('album');
        
        $model->publish($album);
        $this->setRedirect('index.php?view=album&option='.JRequest::getVar('option'));
    }
    
    /**
     * Function to cancel the current operation.
     * 
     * @return  cancel
     */ 
 	function cancel()
    {
        $this->setRedirect('index.php?view=album&option='.JRequest::getVar('option'), 'Cancelled...');
    }
    
    /**
     * Function is to delete the albums.
     * 
     * @return  remove
     */ 
 	function remove()
    {
        //Reads cid as an array
        $arrayIDs 	= JRequest::getVar('cid', null, 'default', 'array' );
        
        if($arrayIDs === null) { 
           //Make sure the cid parameter was in the request
           JError::raiseError(500, 'cid parameter missing from the request');
        }
        
        $model 	= $this->getModel('album');
        
        $model->deleteAlbum($arrayIDs);
        $this->setRedirect('index.php?view=album&option='.JRequest::getVar('option'), 'Deleted...');

    }
}