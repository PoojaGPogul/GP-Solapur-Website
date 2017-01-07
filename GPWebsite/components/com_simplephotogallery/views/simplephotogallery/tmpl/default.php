<?php
/**
 * Images View file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined ( '_JEXEC' ) or die ( 'restricted access' );

// Import joomla view library
jimport ( 'joomla.application.component.view' );

// Get document object and load css,js files
$baseURL = JURI::Base ();
$document = JFactory::getDocument ();

$document->addStyleSheet ( $baseURL . 'components/com_simplephotogallery/css/pica_Carousel_with_autoscrolling_files/skin.css' );

if (version_compare ( JVERSION, '3.0.0', 'ge' )) {
	JHtml::_ ( 'jquery.framework' );
} else {
	$document->addScript ( $baseURL . 'components/com_simplephotogallery/js/pica_Carousel_with_autoscrolling_files/jquery-1.4.2.min.js' );
}

$document->addScript ( $baseURL . 'components/com_simplephotogallery/js/pica_Carousel_with_autoscrolling_files/jquery.jcarousel.min.js' );
$document->addScript ( $baseURL . 'components/com_simplephotogallery/js/gallery.js' );

$session = JFactory::getSession ();
$session->set ( 'backlist', 'feature' );
$mymessage = $session->get ( 'backlist' );

// Fetch details from model file
$album = $this->album;
$images = $this->images;
$settings = $this->settings;
$totalAlbum = $this->totalAlbum;

if (! empty ( $totalAlbum )) {
	$totalAlbum = $totalAlbum [0];
}

// Fetch settings data from settings object
$isShowAlbum = $settings [0]->general_show_alb;

$featVspace = $settings [0]->feat_vspace;
$featHspace = $settings [0]->feat_hspace;
$albVspace = $settings [0]->alb_vspace;
$albHspace = $settings [0]->alb_hspace;

$featWidth = $settings [0]->feat_photo_width;
$featHeight = $settings [0]->feat_photo_height;
$albWidth = $settings [0]->alb_photo_width;
$albHeight = $settings [0]->alb_photo_height;
?>

<h1 class="entry-title"><?php echo  JText::_('SPG_FEATURED_PHOTOS');?></h1>

<div class="featuredPhoto">

<?php

if (count ( $images ) > 0) {
	$a = "";
	
	for($i = 0; $i < count ( $images ); $i ++, $a ++) {
		if ($a >= $settings [0]->feat_cols) 		// for showing number of photos per row
		{
			$a = 0;
			echo "<div style = 'clear:both;'></div>";
		}
		
		$imageURL = JRoute::_ ( "index.php?option=com_simplephotogallery&view=images&albumid=" . $images [$i]->album_id . "&photoid=" . $images [$i]->sortorder );
		?>
				<a href="<?php echo $imageURL;?>">
		<div style="cursor:pointer;float: left;box-shadow:2px 2px 8px #888888;margin: <?php echo $featVspace.'px '.$featHspace.'px' ?>;width: <?php echo $featWidth;?>px">
			<img height="<?php echo $featHeight;?>"
				width="<?php echo $featWidth;?>"
				src="<?php echo JURI::base().'images/photogallery/featured_image/'.$images[$i]->image;?>">
			<br> <span style="word-wrap: break-word;"> <?php //echo ucfirst($images[$i]->title); ?> </span>
		</div>
	</a>
<?php
	
}
} else {
	echo JText::_ ( 'SPG_NO_FEATURED_PHOTOS' );
}
?>
</div>

<div style='clear: both;'></div>

<?php

if ($isShowAlbum == '1') {
	?>
<h1> <?php echo JText::_('SPG_ALBUMS');?> </h1>

<?php
	if (count ( $album ) < 4) {
		?>
<style>
.jcarousel-next,.jcarousel-prev {
	display: none !important;
}
</style>
<?php
	}
	
	if (count ( $album ) > 0) {
		?>
<div id="wrap" style="width:<?php echo (3*$albWidth)+(6*$albHspace)+5;?>px">
	<ul id="mycarouselPhoto" class="jcarousel-skin-tango1">
					
<?php
		
for($j = 0; $j < count ( $album ); $j ++) {
			$albURL = JRoute::_ ( "index.php?option=com_simplephotogallery&view=images&albumid=" . $album [$j]->id );
			?>
								<li style="background:none !important;cursor:pointer;padding:0px !important;float:left;list-style:none outside none !important;margin:<?php echo $albVspace.'px '.$albHspace?>px" >									
									
<?php 								$strUrl = 'index.php?option=com_simplephotogallery&view=images&albumid='.$album[$j]->id; ?>			
									<a class="album-thumb" href="<?php echo JRoute::_($strUrl)?>"
			style="padding-top: 1px; text-decoration: none; background: none !important;">

<?php
			
if ($album [$j]->image != '') {
				echo "<img style='box-shadow:1px 1px 2px #888888;' width='" . $albWidth . "px' height='" . $albHeight . "px' src='" . JURI::base () . 'images/photogallery/album_image/' . $album [$j]->image . "'>";
			} else {
				echo "<img width='" . $albWidth . "px' height='" . $albHeight . "px' src='" . JURI::base () . "components/com_simplephotogallery/images/default_star.gif'>";
			}
			?>
									
									<br> <b><?php echo ucfirst($album[$j]->album_name); ?></b>
		</a>
		</li>
<?php 					} ?>
					</ul>
</div>
<?php
	
} else {
		echo JText::_ ( 'SPG_NO_ALBUMS' );
	}
}
?>

<div style='clear: both;'></div>

<script type="text/javascript">
    function changepage(albumid)
    { 
        window.location.href = '<?php echo JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=")?>'+albumid;
    }
</script>
