<?php
/**
 * Main controller file for Simple Photo Gallery
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
 
JLoader::register('SimplePhotoGalleryController', JPATH_COMPONENT . '/helpers/controller.php');
JLoader::register('SimplePhotoGalleryView', JPATH_COMPONENT . '/helpers/view.php');
JLoader::register('SimplePhotoGalleryModel', JPATH_COMPONENT . '/helpers/model.php');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_simplephotogallery/css/adminstyle.min.css');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

$controllerName = JRequest::getVar('view');

if(empty($controllerName)){
	$controllerName = 'cpanel';
}

// Initialize to false and change to true according to current menu.
$album_active = $images_active = $settings_active = false;

switch ($controllerName)
{
	case "cpanel":
		$album_active = $images_active = $settings_active = false;
		break;
	case "album":
		$album_active = true;
		break;
	case "images":
		$images_active = true;
		break;
	case "settings":		
		$settings_active = true;
		break;
	default:
		$controllerName = 'cpanel';
}

// Adding menus
JSubMenuHelper::addEntry(JText::_('Albums'), JRoute::_('index.php?option=com_simplephotogallery&view=album'),$album_active);
JSubMenuHelper::addEntry(JText::_('Images'), JRoute::_('index.php?option=com_simplephotogallery&view=images'),$images_active);
JSubMenuHelper::addEntry(JText::_('Settings'), JRoute::_('index.php?option=com_simplephotogallery&view=settings'),$settings_active);

if($controllerName) {     
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php';
    
    if (file_exists($path)) {
    	require_once $path;
    } else {
        $controllerName = 'cpanel';
    }
}
 
// Create the controller
$classname    = 'SimplephotogalleryController'.$controllerName;
$controller   = new $classname( );
 
// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );
 
// Redirect if set by the controller
$controller->redirect();