<?php 
/**
 * Control panel controller file for Simple Photo Gallery
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
jimport('joomla.application.component.controller');

/**
 * Control Panel Controller
 * 
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryControllercpanel extends SimplePhotoGalleryController
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
    	if(JRequest::getVar('view') == '')
    	{
    		JRequest::setVar('view','cpanel');
    	}
    	
    	parent::display();
    }
}