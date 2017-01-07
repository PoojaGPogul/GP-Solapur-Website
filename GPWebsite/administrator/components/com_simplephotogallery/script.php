<?php
/**
 * Installation file of Simple photo gallery
 *
 * This file is to install component, modules and plugin
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    3.6
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import Joomla installer library
jimport('joomla.installer.installer');

// Import Joomla environment library
jimport('joomla.environment.uri');

/**
 * Component Simple photo gallery installer file
 *
 * @package     Joomla.Simplephotogallery
 * @subpackage  Com_Simplephotogallery
 * @since       1.5
 */
class Com_SimplephotogalleryInstallerScript
{
	/**
	 * Joomla installation hook for component
	 * 
	 * @param   string  $parent  parent value
	 * 
	 * @return  install
	 */
	public function install($parent)
	{
	}

	/**
	 * Joomla uninstallation hook for component
	 * 
	 * @param   string  $parent  parent value
	 * 
	 * @return  uninstall
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * Joomla before installation hook for component
	 * 
	 * @param   string  $type    type
	 * @param   string  $parent  parent value
	 * 
	 * @return  preflight
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * Joomla after installation hook for component
	 * 
	 * @param   string  $type    type
	 * @param   string  $parent  parent value
	 * 
	 * @return  postflight
	 */
	public function postflight($type, $parent)
	{
		?>
		
		<style>
		table.adminform th{padding:0px;}
		</style>
		<div style="/* Opera 11.10+ */
		background: -o-linear-gradient(top, rgba(255,255,255,1), rgba(232,232,232,1));
		
		/* Firefox 3.6+ */
		background: -moz-linear-gradient(top, rgba(255,255,255,1), rgba(232,232,232,1));
		
		/* Chrome 7+ & Safari 5.03+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0, rgba(255,255,255,1)), color-stop(1, rgba(232,232,232,1)));
		
		/* IE5.5 - IE7 */
		filter: progid:DXImageTransform.Microsoft.Gradient(GradientType=0,StartColorStr=#FFFFFFFF,EndColorStr=#FFE8E8E8);
		
		/* IE8 */
		-ms-filter: progid:DXImageTransform.Microsoft.Gradient(GradientType=0,StartColorStr=#FFFFFFFF,EndColorStr=#FFE8E8E8);">
		<div>
		<div style="float: left; width: 80%;">
		<p style="font-style:normal;font-size:13px;font-weight:normal; margin-top:10px;margin-left:10px;">Simple photo gallery is an extension which shows up your photos in an impressive and memorable way. The gallery extension easily organizes your large set of digital photos, edit images and create multiple albums.</p>
		</div>
		<div style="float: right; margin-top: 19px; margin-right: 10px;">
		        <a href="https://www.apptha.com/category/extension/Joomla/Simple-Photo-Gallery" target="_blank">
		            <img src="components/com_simplephotogallery/images/contus.jpg" alt="Joomla! Simple photo gallery" align="left" />
		        </a>
		    </div>
		</div>
		
		    <div style="clear:both;"></div>
		    <div style="margin-left:10px;padding-bottom:10px;">
		        <a href="https://docs.google.com/a/contus.in/viewer?url=https://www.apptha.com/downloadable/download/sample/sample_id/102/Simple+Photo+Gallery" target="_blank">Click here to download the documentation</a><br/>
		    </div>
		    
		    </div>
		    <?php
	}
}
