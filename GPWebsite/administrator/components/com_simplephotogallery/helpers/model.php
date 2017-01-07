<?php
/**
 * Model file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import Joomla model library
jimport('joomla.application.component.model');

if (version_compare(JVERSION, '3.0', 'ge'))
{
	/**
	 * SimplaPhotoGalleryModel class.
	 *
	 * @package     Joomla.Simple_Photo_Gallery
	 * @subpackage  com_simplephotogallery
	 * @since       1.5
	 */
	class SimplePhotoGalleryModel extends JModelLegacy
	{
	}
}
elseif (version_compare(JVERSION, '2.5', 'ge'))
{
	/**
	 * SimplaPhotoGalleryModel class.
	 *
	 * @package     Joomla.Simple_Photo_Gallery
	 * @subpackage  com_simplephotogallery
	 * @since       1.5
	 */
	class SimplePhotoGalleryModel extends JModel
	{
	}
}
elseif(version_compare(JVERSION, '1.6', 'ge') || version_compare(JVERSION, '1.7', 'ge'))
{
	/**
	 * SimplaPhotoGalleryModel class.
	 *
	 * @package     Joomla.Simple_Photo_Gallery
	 * @subpackage  com_simplephotogallery
	 * @since       1.5
	 */
	class SimplePhotoGalleryModel extends JModel
	{
	}
}
else
{
	/**
	 * SimplaPhotoGalleryModel class.
	 *
	 * @package     Joomla.Simple_Photo_Gallery
	 * @subpackage  com_simplephotogallery
	 * @since       1.5
	 */
	class SimplePhotoGalleryModel extends JModel
	{
	}
}
?>