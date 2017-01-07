<?php
/**
 * Component file to invoke controller for Simple Photo Gallery
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
 
JLoader::register('SimplePhotoGalleryController', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/controller.php');
JLoader::register('SimplePhotoGalleryView', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/view.php');
JLoader::register('SimplePhotoGalleryModel', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/model.php');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
if ($controller = JRequest::getWord('controller')) {
	$path = JPATH_COMPONENT.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}else{
	$controller = 'simplephotogallery';
}

// Create the controller
$classname    = 'SimplephotogalleryController'.$controller;
$controller   = new $classname( );
 
// Perform the Request task
$taskconfig	= "";
$taskconfig	= JRequest::getvar('taskconfig');
if($taskconfig)
{
    $controller->configxml();
}
else
{
	$controller->execute( JRequest::getVar('task'));
}

// Redirect if set by the controller
$controller->redirect();
?>
Powered by <a href ="http://www.apptha.com" target="_blank">Apptha</a>