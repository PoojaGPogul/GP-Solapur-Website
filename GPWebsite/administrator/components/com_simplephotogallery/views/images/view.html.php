<?php
/**
 * Images view file for Simple Photo Gallery
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
 * Images Administrator view
 *
 * @package Joomla.Simple_Photo_Gallery
 * @subpackage com_simplephotogallery
 * @since 1.5
 */
class SimplephotogalleryViewImages extends SimplePhotoGalleryView {

	/**
	 * Function to get/display Images list from images table.
	 *
	 * @param   boolean  $tpl   If true, the view output will be cached
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	function display($tpl = null) {
		if (JRequest::getVar ( 'task' ) == 'edit') {

			JToolBarHelper::save ( "saveclose" );
			JToolBarHelper::apply ();
			JToolBarHelper::cancel ();

			$model = $this->getModel ();
			$id = JRequest::getVar ( 'cid' );
			$images = $model->getimages ( $id [0] );
			$this->assignRef ( 'images', $images );

			parent::display ();
		} else if (JRequest::getVar ( 'task' ) == 'add') {

			JToolBarHelper::save ();
			JToolBarHelper::save ( "savenew", "Save &amp; new" );
			JToolBarHelper::cancel ();

			$model = $this->getModel ();
			$settings = $model->settings ();
			$images = $model->getNewimages ();
			$this->assignRef ( 'images', $images );
			$this->assignRef ( 'settings', $settings );

			parent::display ();
		} else {

			JToolBarHelper::publishList ( 'featuredpublish', 'Featured' );
			JToolBarHelper::unpublishList ( 'featuredunpublish', 'Unfeatured' );
			JToolBarHelper::publishList ();
			JToolBarHelper::unpublishList ();
			JToolBarHelper::deleteList ();
			JToolBarHelper::editList ();
			JToolBarHelper::addNew ();
			$model = $this->getModel ();

			if (JRequest::getVar ( 'set' ) != '') {
				$set = $model->setImage ();
				$this->assignRef ( 'set', $set );
			}

			$images = $model->getimage ();
			$this->assignRef ( 'images', $images );

			parent::display ();
		}

		JToolBarHelper::title ( JText::_ ( 'Simple Photo Gallery' ) . ': [ <small> ' . JText::_ ( 'Images' ) . ' </small> ]' );
	}

	/**
	 * This function is to publish/unpublish images
	 *
	 * @param  array   $row     image detail
	 * @param  int     $i       image id
	 * @param  string  $imgY    success image
	 * @param  string  $imgX    publish image
	 * @param  string  $prefix  prefix
	 * 
	 * @return  string
	 */
	function published($row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix = 'download') {		
		$app = JFactory::getApplication ();
		$templateName = $app->getTemplate ();

		if (version_compare ( JVERSION, '1.6.0', 'ge' )) {
			$route = JURI::base () . 'templates/' . $templateName . '/images/admin/';
		} else {
			$route = JURI::base () . 'images/';
		}

		$img = $row ? $imgY : $imgX;
		$task = $row ? 'unpublish' : 'publish';
		$alt = $row ? JText::_ ( 'Published' ) : JText::_ ( 'Unpublished' );
		$action = $row ? JText::_ ('Disable Featured') : JText::_ ( 'Enable Featured');

		$href = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $prefix . $task . '\')" title="' . $action . '">
        <img src="' . $route . $img . '" border="0" alt="' . $alt . '" /></a>';

		return $href;
	}
}