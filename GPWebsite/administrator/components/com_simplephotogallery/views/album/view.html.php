<?php
/**
 * Album view file for Simple Photo Gallery
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
 * Album Administrator view
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class SimplephotogalleryViewAlbum extends SimplePhotoGalleryView {

	/**
	 * Function to get/display album list from album table.
	 *
	 * @param   boolean  $tpl   If true, the view output will be cached
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	function display($tpl = null) {
		$this->toolbar ();
		$model = $this->getModel ( "album" );
		$album = $model->getAlbum ();
		
		if (JRequest::getVar ( 'task' ) == 'edit') {
			$id = JRequest::getVar ( 'cid' );
			$album = $model->getAlbums ( $id [0] );
			$this->assignRef ( 'album', $album );
		} else {
			$this->assignRef ( 'album', $album );
		}

		parent::display ( $tpl );
	}

	/**
	 * Function to Setting the toolbar
	 * 
	 * @return  addToolBar
	 */
	function toolbar() {
		if (JRequest::getVar ( 'task' ) == 'add' || JRequest::getVar ( 'task' ) == 'edit') {
			JToolBarHelper::save ();
			JToolBarHelper::cancel ();
		} else {
			JToolBarHelper::publishList ();
			JToolBarHelper::unpublishList ();
			JToolBarHelper::deleteList ();
			JToolBarHelper::editList ();
			JToolBarHelper::addNew ();
		}

		JToolBarHelper::title ( JText::_ ( 'Simple Photo Gallery' ) . ': [ <small> ' . JText::_ ( 'Album' ) . ' </small> ]' );
	}
}