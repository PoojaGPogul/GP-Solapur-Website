<?php
/**
 * Settings view file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Settings Administrator view
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class SimplephotogalleryViewSettings extends SimplePhotoGalleryView {

	/**
	 * Function to get/display Settings
	 *
	 * @param   boolean  $tpl   If true, the view output will be cached
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	function display($tpl = null) {
		if (JRequest::getVar ( 'task' ) == 'edit') {
			JToolBarHelper::save ();
			JToolBarHelper::apply ();
			JToolBarHelper::cancel ();
			
			$model = $this->getModel ();
			$settings = $model->getsettings ();
			$this->assignRef ( 'settings', $settings );
			
			parent::display ();
		} else if (JRequest::getVar ( 'task' ) == '') {
			
			JToolBarHelper::custom ( "regenrate", "move.png", "", JText::_ ( 'Re-Generate Image' ), false );
			JToolBarHelper::custom( "edit", "edit.png", "", JText::_ ( 'Edit' ), false );
			
			$model = $this->getModel ();
			$settings = $model->getsettings ();
			$this->assignRef ( 'settings', $settings );
			
			parent::display ();
		}
		
		JToolBarHelper::title ( JText::_ ( 'Simple Photo Gallery' ) . ': [ <small> ' . JText::_ ( 'Settings' ) . ' </small> ]' );
	}
}
