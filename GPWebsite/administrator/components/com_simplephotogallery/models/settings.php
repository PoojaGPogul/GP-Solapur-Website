<?php
/**
 * Settings model file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct acesss
defined ( '_JEXEC' ) or die ();

// Import joomla model library
jimport ( 'joomla.application.component.model' );

/**
 * Settings Administrator Model
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class SimplephotogalleryModelsettings extends SimplePhotoGalleryModel
{
	/**
	 * This function is to get settings details from the database.
	 *
	 * @return	array
	 */
	function getsettings() {
		global $option, $mainframe;
		$db 	= JFactory::getDBO ();
		$query	= $db->getQuery(true);

		$query->select('*')
			->from('#__simplephotogallery_settings');
		$db->setQuery ( $query );
		$rows = $db->loadObjectList ();
		
		if ($db->getErrorNum ()) {
			echo $db->stderr ();
			return false;
		}

		$settings = array (
				'option' => $option,
				'row' => $rows 
		);

		return $settings;
	}

	/**
	 * This function is to save settings details
	 *
	 * @return	void
	 */
	function savesettings($settings) {
		$settingsTableRow = $this->getTable ( 'settings' );

		if (! $settingsTableRow->bind ( $settings )) {
			JError::raiseError ( 500, 'Error binding data' );
		}

		if (! $settingsTableRow->check ()) {
			JError::raiseError ( 500, 'Invalid data' );
		}

		if (! $settingsTableRow->store ()) {
			$errorMessage = $settingsTableRow->getError ();
			JError::raiseError ( 500, 'Error binding data: ' . $errorMessage );
		}
	}
}
