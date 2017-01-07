<?php 
/**
 * Settings controller file for Simple Photo Gallery
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
 * Settings Administrator Controller
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryControllersettings extends SimplePhotoGalleryController
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
     * This function is to save image and album settings and redirect to settings display page.
     *
     * @return  save
     */    
    function save()
    {
        $detail = JRequest::get( 'POST' );
        
        $model 	= $this->getModel('settings');
        $model->savesettings($detail);
        
        $this->setRedirect('index.php?view=settings&option='.JRequest::getVar('option'), JText::_('Settings saved.').$msg);
    }
    
    /**
     * This function is to save image and album settings and remains in edit setting page.
     *
     * @return  apply
     */    
	function apply()
    {
        $detail = JRequest::get( 'POST' );
        
        $model 	= $this->getModel('settings');
        $model->savesettings($detail);
        
        $link = 'index.php?option=com_simplephotogallery&view=settings&task=edit&cid[]='.$settings['id'];
        $this->setRedirect($link, JText::_('Settings saved..').$msg);
    }
    
    /**
     * Function to cancel the current operation.
     *
     * @return  cancel
     */ 
 	function cancel()
    {
        $this->setRedirect('index.php?view=settings&option='.JRequest::getVar('option'), JText::_('Cancelled..'));
    }
    
    /**
     * Function to regenerate the image size in folder as per the settings value.
     *
     * @return  cancel
     */
	function regenrate() 
	{
		// open the directory
	    $pathToImages = JPATH_ROOT.DS."images".DS."photogallery".DS;
	        
	    ini_set("memory_limit", "1000M");
	    $array 	= array("full_image","featured_image","album_image","thumb_image");
	        
	    $model 	= $this->getModel('settings');
    	$result = $model->getsettings();
    	$row 	= $result["row"][0];
    	
    	for($f=0; $f<count($array); $f++)
	    {	        
	    	$pathToThumbs = JPATH_ROOT.DS."images".DS."photogallery".DS . $array[$f] . DS;
	        $dir = opendir($pathToImages);
	        
	        // Loop through it, looking for any/all JPG files:
	        while (false !== ($fname = readdir($dir))) 
	        {
	        	// parse path for the extension
	            $info = pathinfo($pathToImages . $fname);

	            if($fname !="." && $fname !=".." )
	            {
		            if (strtolower($info['extension']) == 'jpg') 
		            {
		                // Load image and get image size
		                $img = imagecreatefromjpeg("{$pathToImages}{$fname}");
		            } 
		            else if (strtolower($info['extension']) == 'png') 
		            {
		                // Load image and get image size
		                $img = imagecreatefrompng("{$pathToImages}{$fname}");
		            } 
		            else if (strtolower($info['extension']) == 'gif') 
		            {
		                // Load image and get image size
		                $img = imagecreatefromgif("{$pathToImages}{$fname}");
		            }
	        		else if (strtolower($info['extension']) == 'bmp') 
	        		{
		                // Load image and get image size
		                $img = imagecreatefromwbmp("{$pathToImages}{$fname}");
		            }
		            
		            $width 	= imagesx($img);
		            $height = imagesy($img);
		                
		            if($array[$f] == "full_image")
		            {
						$new_width 	= $row->fullimg_width;
		                $new_height = $row->fullimg_height;
					}
					else if($array[$f] == "featured_image")
		            {
		            	$new_width 	= $row->feat_photo_width;
		                $new_height = $row->feat_photo_height;
		            }
		            else if($array[$f] == "album_image")
	        		{
		            	$new_width 	= $row->alb_photo_width;
		                $new_height = $row->alb_photo_height;
					}
		            else if($array[$f] == "thumb_image")
		            {
		            	$new_width 	= $row->thumbimg_width; ;
		                $new_height = $row->thumbimg_height;;
					}

					// Create a new temporary image
					$tmp_img = imagecreatetruecolor($new_width, $new_height);
					
					// Copy and resize old image into new image
		            imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		            
		            if (strtolower($info['extension']) == 'jpg') 
		            {
			        	// Save thumbnail into a file
						imagejpeg($tmp_img, "{$pathToThumbs}{$fname}");
					}
					else if (strtolower($info['extension']) == 'png') 
					{
			            // Save thumbnail into a file
			            imagepng($tmp_img, "{$pathToThumbs}{$fname}");
			        }
			        else if (strtolower($info['extension']) == 'gif') 
			        {
			            // Save thumbnail into a file
			            imagegif($tmp_img, "{$pathToThumbs}{$fname}");
			        }
	        		else if (strtolower($info['extension']) == 'gif') 
	        		{
			        	// Save thumbnail into a file
			            image2wbmp($tmp_img, "{$pathToThumbs}{$fname}");
			        }
	            }
	        }

	        // close the directory
	        closedir($dir);		       
	    }
	    
		$this->setRedirect('index.php?view=settings&option='.JRequest::getVar('option'), JText::_('Images regenerated successfully') );
	}
}