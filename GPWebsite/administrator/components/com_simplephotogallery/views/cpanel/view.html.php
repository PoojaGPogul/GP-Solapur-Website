<?php
/**
 * Control panel view file for Simple Photo Gallery
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
 * Control Panel Administrator view
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class SimplephotogalleryViewcpanel extends SimplePhotoGalleryView {

	/**
	 * Function to display Control Panel
	 *
	 * @param   boolean  $tpl   If true, the view output will be cached
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	function display($tpl = NULL) {
		JToolBarHelper::title ( JText::_ ( 'Simple Photo Gallery' ) . ':  <small> ' . JText::_ ( 'Control Panel' ) . ' </small> ' );
		parent::display ();
	}
}