<?php
/**
 * Upload file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

macgallery_upload ();
exit ();

/**
 * This function is to upload images
 *
 * @return string
 *
 */
function macgallery_upload() {
	$fieldName = 'uploadfile';
	
	// Any errors the server registered on uploading
	$fileError = $_FILES [$fieldName] ['error'];

	if ($fileError > 0) {
		switch ($fileError) {
			case 1 :
				JError::raiseWarning ( E_ERROR, JText::_('COM_MACGALLERY_CONTROLLERS_FILELARGEPHP') );
				break;
			
			case 2 :
				JError::raiseWarning ( E_ERROR, JText::_('COM_MACGALLERY_CONTROLLERS_FILELARGEHTML') );
				break;
			
			case 3 :
				JError::raiseWarning ( E_ERROR, JText::_('COM_MACGALLERY_CONTROLLERS_FILEERROR') );
				break;
			
			case 4 :
				JError::raiseWarning ( E_ERROR, JText::_('COM_MACGALLERY_CONTROLLERS_FILEERROR') );
				break;
		}
	}
	
	// Check for filesize
	$fileSize = $_FILES [$fieldName] ['size'];

	if ($fileSize > 20000000) {
		echo JText::_('COM_MACGALLERY_BIGGER_THAN_20_MB');
	}
	
	// Check the file extension is ok
	$fileName = $_FILES [$fieldName] ['name'];
	$uploadedFileNameParts = explode ( '.', $fileName );
	$uploadedFileExtension = array_pop ( $uploadedFileNameParts );
	$validFileExts = explode ( ',', 'jpeg,jpg,png,gif,bmp' );
	
	// Assume the extension is false until we know its ok
	$extOk = false;
	
	// Check for valid extension
	foreach ( $validFileExts as $key => $value ) {
		if (preg_match ( "/$value/i", $uploadedFileExtension )) {
			$extOk = true;
		}
	}
	
	/* The name of the file in PHP's temp directory that we are going to move to our folder
	 * for security purposes, we will also do a getimagesize on the temp file (before we have moved it 
	 * to the folder) to check the MIME type of the file, and whether it has a width and height 
	 * lose any special characters in the filename */
	
	for($code_length = 5, $newcode = ''; strlen ( $newcode ) < $code_length; $newcode .= chr ( ! rand ( 0, 2 ) ? rand ( 48, 57 ) : (! rand ( 0, 1 ) ? rand ( 65, 90 ) : rand ( 97, 122 )) ));
	
	$fileTemp = $_FILES [$fieldName] ['tmp_name'];
	$fileParts = explode ( ".", trim ( $_FILES [$fieldName] ['name'] ) );
	$fileExtension = $fileParts [count ( $fileParts ) - 1];
	$fileName = preg_replace ( "[^A-Za-z0-9.]", "-", $fileName );
	$fileName = $fileParts [0] . "__" . $newcode . rand ( 1, 100000 ) . "." . $fileExtension;
	
	// Always use constants when making file paths, to avoid the possibilty of remote file inclusion
	$uploadPath = urldecode ( $_REQUEST ["jpath"] ) . $fileName;
	
	if (! move_uploaded_file ( $fileTemp, $uploadPath )) {
		echo 'COM_MACGALLERY_CONTROLLERS_MOVEERROR';
	}

	echo $fileName;

	// Crating thumb image
	$thumbWidth = ( int ) $_REQUEST ["th"] + ( int ) $_REQUEST ["tw"] + 50;
	imageToThumb ( $fileName, $_REQUEST ["th"], $_REQUEST ["tw"], "thumb_image" );
	imageToThumb ( $fileName, $_REQUEST ["feath"], $_REQUEST ["featw"], "featured_image" );
	imageToThumb ( $fileName, $_REQUEST ["ah"], $_REQUEST ["aw"], "album_image" );
	imageToThumb ( $fileName, $_REQUEST ["fh"], $_REQUEST ["fw"], "full_image" );
}

/**
 * This function is to create Thumbnail image from original image.
 *
 * @return void
 *
 */
function imageToThumb($fname, $imgheight, $imgwidth, $foldername) {
	// Open the directory
	$pathToImages = urldecode ( $_REQUEST ["jpath"] );
	$pathToThumbs = urldecode ( $_REQUEST ["jpath"] ) . $foldername . "/";
	$dir = opendir ( $pathToImages );
	ini_set ( "memory_limit", "1000M" );

	// Loop through it, looking for any/all JPG files:
	if (readdir ( $dir )) {
		// Parse path for the extension
		$info = pathinfo ( $pathToImages . $fname );
		
		if (strtolower ( $info ['extension'] ) == 'jpg') {
			// Load image and get image size
			$img = imagecreatefromjpeg ( "{$pathToImages}{$fname}" );
		} else if (strtolower ( $info ['extension'] ) == 'png') {
			// Load image and get image size
			$img = imagecreatefrompng ( "{$pathToImages}{$fname}" );
		} else if (strtolower ( $info ['extension'] ) == 'gif') {
			// Load image and get image size
			$img = imagecreatefromgif ( "{$pathToImages}{$fname}" );
		}

		$width = imagesx ( $img );
		$height = imagesy ( $img );
		
		/* Calculate thumbnail size
		 * $new_width = $thumbWidth;
		 * $new_height = floor($height * ( $thumbWidth / $width )); */
		
		$new_width = $imgwidth;
		$new_height = $imgheight;
		
		// Create a new temporary image
		$tmp_img = imagecreatetruecolor ( $new_width, $new_height );
		
		// Copy and resize old image into new image
		imagecopyresized ( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		
		if (strtolower ( $info ['extension'] ) == 'jpg') {
			// Save thumbnail into a file
			imagejpeg ( $tmp_img, "{$pathToThumbs}{$fname}" );
		} else if (strtolower ( $info ['extension'] ) == 'png') {
			// Save thumbnail into a file
			imagepng ( $tmp_img, "{$pathToThumbs}{$fname}" );
		} else if (strtolower ( $info ['extension'] ) == 'gif') {
			// Save thumbnail into a file
			imagegif ( $tmp_img, "{$pathToThumbs}{$fname}" );
		}
	}

	// Close the directory
	closedir ( $dir );
}
