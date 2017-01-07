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

// No direct acesss
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.html.pane' );

function quickiconButton($link, $image, $text) {
	global $mainframe;
	$lang = JFactory::getLanguage ();
	?>
<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a href="<?php echo $link; ?>">
				<?php echo JHTML::image(JURI::base ().'components/com_simplephotogallery/images/'.$image, $text, NULL, true, false ); ?>
			<span><?php echo $text; ?></span>
		</a>
	</div>
</div>
<?php
}
?>
<div class="contus-contropanel">
	<h2 class="heading">Simple Photo Gallery Control panel</h2>
</div>
<!-- Control panel first column view start here -->
<div class="cpanel-left">
	<div class="banner">
		<a href="http://www.apptha.com" target="_blank"><img
				src="components/com_simplephotogallery/images/apptha-banner.jpg"
				width="485" height="94" alt=""> </a>
	</div>
	
	<div id="cpanel">

		<div class="icon">
			<a
				href="<?php echo JRoute::_("index.php?option=com_simplephotogallery&view=album"); ?>"
				title="Albums"> <img
					src="<?php echo JURI::base ().'components/com_simplephotogallery/images/albums.png'; ?>"
					title="Albums" alt="Albums"> <span>Albums</span>
			</a>
		</div>

		<div class="icon">
			<a
				href="<?php echo JRoute::_("index.php?option=com_simplephotogallery&view=images"); ?>"
				title="Images"> <img
					src="<?php echo JURI::base ().'components/com_simplephotogallery/images/images.png'; ?>"
					title="Images" alt="Images"> <span>Images</span>
			</a>
		</div>

		<div class="icon">
			<a
				href="<?php echo JRoute::_("index.php?option=com_simplephotogallery&view=settings"); ?>"
				title="Settings"> <img
					src="<?php echo JURI::base ().'components/com_simplephotogallery/images/settings-icon.png'; ?>"
					title="Settings" alt="Settings"> <span>Settings</span> </a>
		</div>
	</div>
	
	</div>
