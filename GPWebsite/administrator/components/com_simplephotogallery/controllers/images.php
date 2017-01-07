<?php 
/**
 * Images controller file for Simple Photo Gallery
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

// Import joomla file system library
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Images Administrator Controller  
 * 
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */ 
class SimplephotogalleryControllerimages extends SimplePhotoGalleryController
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
     * This function is to save new images and redirect to same page.
     *
     * @return  save
     */
	function save()
    {
    	
    	$images 	= JRequest::get('POST');

        for($inc=0; $inc < count($images["image"]); $inc++)
        {
        	$model 	= $this->getModel('images');
        	
        	$imageDetails["image"] = $images["image"][$inc];
        	$imageDetails["title"] = $images["title"][$inc];
        	
        	if(!empty($images['alias_name'][$inc])) 
        	{
        		$aliasName 	= strtolower(trim($images["alias_name"][$inc]));
				$aliasName 	= str_replace(' ','-',$aliasName);
				
        		$imageDetails["alias_name"] = $aliasName;
        	} 
        	else 
        	{
        		$aliasName = strtolower(trim($images["title"][$inc]));
				$aliasName = str_replace(' ','-',$aliasName);
				
        		$imageDetails["alias_name"] = $aliasName;
        	}
        	
        	$imageDetails["album_id"] 		= $images["albumid"];
        	$imageDetails["published"] 		= "1";
        	$imageDetails["description"] 	= $images["imagedesc"][$inc];
        	
        	$model->saveImagesNew($imageDetails);
        }
        
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".$imageDetails["album_id"], 'Saved!');
    }
    
    /**
     * This function is to save new images and redirect to add images section.
     *
     * @return  save
     */
	function savenew()
	{
            $this->save();
            $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&task=add"."&albumid=".JRequest::getVar('albumid'), 'Saved!');
    }
    
    /**
     * This function is to save new images and redirect to imageslist grid section.
     *
     * @return  save
     */
	function saveclose()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->saveimages($images);
        
        $link	= 'index.php?option='.JRequest::getVar('option').'&view=images&albumid='.$images['album_id'];
        $this->setRedirect($link, 'Image Saved!');
    }
    
    /**
     * This function is to apply the selected operation and redirect to imageslist grid section.
     *
     * @return  apply
     */
	function apply()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->saveimages($images);
        
        $link 	= 'index.php?option='.JRequest::getVar('option').'&view=images&task=edit&cid[]='.$images['id'];
        $this->setRedirect($link, 'Image Saved!');
    }
    
    /**
     * Function to publish the item from the listing.
     *
     * @return  publish
     */
	function publish()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->pubimages($images);
        
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".JRequest::getInt('albumid'));
    }

    /**
     * Function to unpublish the item from the listing.
     *
     * @return  unpublish
     */
    function unpublish()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->pubimages($images);
        
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".JRequest::getInt('albumid'));
    }
    
    /**
     * Function to upload new images.
     *
     * @return  upload
     */
    function photo_upload()
	{		
		// This is the name of the field in the html form, filedata is the default name for swfupload	        
	    $fieldName = 'upload-file';
	
	    // Any errors the server registered on uploading
	    $fileError = $_FILES[$fieldName]['error'];
	        
	    if ($fileError > 0)
	    {
	    	switch ($fileError)
	        {
	        	case 1:
	            	JError::raiseWarning("", JText::_( 'COM_MACGALLERY_CONTROLLERS_FILELARGEPHP' ));
	                break;
	
				case 2:
	                JError::raiseWarning("", JText::_( 'COM_MACGALLERY_CONTROLLERS_FILELARGEHTML' ));
	                break;
	
	            case 3:
	            	JError::raiseWarning("", JText::_( 'COM_MACGALLERY_CONTROLLERS_FILEERROR' ));
	                break;
	
				case 4:
	            	JError::raiseWarning("", JText::_( 'COM_MACGALLERY_CONTROLLERS_FILEERROR' ));
	                break;
			}
		}
	
	    // Check for filesize
	    $fileSize = $_FILES[$fieldName]['size'];
	    
	    if($fileSize > 20000000)
	    {
	    	JError::raiseWarning("", JText::_( 'COM_MACGALLERY_BIGGER_THAN_20_MB' ));
	    }
	
	    // Check the file extension is ok
	    $fileName 				= $_FILES[$fieldName]['name']; 
	    
	    $uploadedFileNameParts 	= explode('.',$fileName);
	    $uploadedFileExtension 	= array_pop($uploadedFileNameParts);
	
		$validFileExts 			= explode(',', 'jpeg,jpg,png,gif,bmp');
	
		// Assume the extension is false until we know its ok
		$extOk 	= false;
	
		// Check whether the uploaded image have the valid allowed extension 
		foreach($validFileExts as $key => $value)
	    {
	    	if( preg_match("/$value/i", $uploadedFileExtension ) )
	        {
	        	$extOk = true;
	        }
		}

	 	// For security purposes, we will also do a getimagesize on the temp file (before we have moved it
	    // to the folder) to check the MIME type of the file, and whether it has a width and height
	    // lose any special characters in the filename
	    
	    for ($code_length = 5, $newcode = ''; 
	    	strlen($newcode) < $code_length; 
	    	$newcode .= chr(!rand(0, 2) ? rand(48, 57) : 
	    				(!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
	        );
	
	    $fileTemp 		= $_FILES[$fieldName]['tmp_name'];
	    
	    $fileParts 		= explode(".",trim($_FILES[$fieldName]['name']));
	    $fileExtension 	= $fileParts[count($fileParts)-1];
	    
	    $fileName 		= preg_replace("[^A-Za-z0-9.]", "-", $fileName);
	    $fileName 		= $fileParts[0]."__".$newcode.rand(1,100000).".".$fileExtension;
	        
	    // Always use constants when making file paths, to avoid the possibilty of remote file inclusion
	    $uploadPath = JPATH_SITE.DS.'images'.DS."photogallery".DS.$fileName;
	
	    if(!JFile::upload($fileTemp, $uploadPath))
	    {
	    	JError::raiseWarning("", JText::_( 'COM_MACGALLERY_CONTROLLERS_MOVEERROR' ));
	    }
	        
		$model = $this->getModel('images');
    	$result = $model->getsettings();
    	$row = $result["row"][0];

		// Creating thumb image
		$this->imageToThumb($fileName,$row->thumbimg_height,$row->thumbimg_width,"thumb_image");
    	$this->imageToThumb($fileName,$row->alb_photo_height,$row->alb_photo_width,"album_image");
    	$this->imageToThumb($fileName,$row->feat_photo_height,$row->feat_photo_width,"featured_image");
    	$this->imageToThumb($fileName,$row->fullimg_height,$row->fullimg_width,"full_image");

    	$link = base64_decode(JRequest::getVar("return-url"));
	    $this->setRedirect($link,$msg);
	}
	
	/**
	 * Function to create Thumbnail image from original image.
	 * 
	 * @param   string  $fname   	New uploaded image name
	 * @param   int  	$imgheight  Image height from settings  
	 * @param   int  	$imgwidth  	Image width from settings 
	 * @param	string	$foldername	Uploaded folder path  
	 */ 
	function imageToThumb($fname,$imgheight,$imgwidth,$foldername) 
	{
		// open the directory
	    $pathToImages = JPATH_ROOT.DS."images".DS."photogallery".DS;
	    $pathToThumbs = JPATH_ROOT.DS."images".DS."photogallery".DS . $foldername . DS;
	    
	    $dir = opendir($pathToImages);
	    ini_set("memory_limit", "1000M");
	    
	    // Loop through it, looking for any/all JPG files:
	    if (readdir($dir)) 
	    {
	    	// Parse path for the extension
	        $info = pathinfo($pathToImages . $fname);

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
	        
	        // Calculate uploaded image size
	        $width 		= imagesx($img);
	        $height 	= imagesy($img);
	        
	        // New thumbnail size
	        $new_width 	= $imgwidth;
	        $new_height = $imgheight;
	        
	        // Create a new temporary image
	        $tmp_img 	= imagecreatetruecolor($new_width, $new_height);
	        
	        // Copy and resize old image into new image
	        imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	        
	        if (strtolower($info['extension']) == 'jpg') {
	        	// save thumbnail into a file
	        	imagejpeg($tmp_img, "{$pathToThumbs}{$fname}");
	        } 
	        else if (strtolower($info['extension']) == 'png') 
	        {
	        	// save thumbnail into a file
	        	imagepng($tmp_img, "{$pathToThumbs}{$fname}");
	        } 
	        else if (strtolower($info['extension']) == 'gif') 
	        {
	        	// save thumbnail into a file
	        	imagegif($tmp_img, "{$pathToThumbs}{$fname}");
	        }
	    }
	    
	    // close the directory
	    closedir($dir);
	}   

	/**
	 * Function to cancel the current operation.
	 *
	 * @return  cancel
	 */
	function cancel()
    {
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option'), 'Cancelled...');
    }
    
    /**
     * Function is to delete the images.
     *
     * @return  remove
     */
	function remove()
    {
    	// Reads cid as an array
        $arrayIDs = JRequest::getVar('cid', null, 'default', 'array' ); 
        
        /* Make sure the cid parameter was in the request */
        if($arrayIDs === null)
        { 
            JError::raiseError(500, 'cid parameter missing from the request');
        }
        
        $model = $this->getModel('images');
        $model->deleteimages($arrayIDs);
        
        if(JRequest::getInt("albumid"))
        {
        	$this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".JRequest::getInt("albumid"), 'Deleted...');	
        }
        else
        {
        	$this->setRedirect('index.php?view=images&option='.JRequest::getVar('option'), 'Deleted...');
        }
        
    }
    
    /**
     * Function to set featured item from the listing.
     *
     * @return  featured
     */
	function featuredpublish()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->featuredpublish($images);
        
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".JRequest::getInt('albumid'));
    }

    /**
     * Function to unset featured item from the listing.
     *
     * @return  unfeatured
     */
    function featuredunpublish()
    {
        $images = JRequest::get('POST');
        
        $model 	= $this->getModel('images');
        $model->featuredpublish($images);
        
        $this->setRedirect('index.php?view=images&option='.JRequest::getVar('option')."&albumid=".JRequest::getInt('albumid'));
    }
}