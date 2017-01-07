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
defined('_JEXEC') or die('restricted access');

// Import joomla view library
jimport('joomla.application.component.view');

$baseURL 	= JURI::base();
$document 	= JFactory::getDocument();

$session = JFactory::getSession();

if(!JRequest::getVar('photoid'))
{
	$session->set('backlist', 'albumPage');
}
$mymessage = $session->get('backlist');

// Initializing Variables
$totalPhotos = $isShare = $isDownload = $isShowAlbum = $api_key = '';
$vspace = $hspace = $albVspace = $albHspace = $featVspace = $featHspace = '';
$twidth = $theight = $fullImageWidth = $fullImageHeight = $albWidth = $albHeight = $featWidth = $featHeight = '';

// Fetch images, album, settings from model 
$album 		= $this->album;
$images 	= $this->images;
$settings 	= $this->settings;

$photoID 		= JRequest::getInt('photoid');
$albumID 		= JRequest::getInt('albumid');

$model 			= $this->getModel();
$totalPhotos 	= $model->totalImage($albumID);
$totalPhotos 	= $totalPhotos[0];

// Get settings details from settings object
if(!empty($settings)){
	$isShare 			= $settings[0]->general_share_photo;
	$isDownload 		= $settings[0]->general_download;
	$isShowAlbum 		= $settings[0]->general_show_alb;
	$api_key 			= $settings[0]->facebook_api_id;
	
	$vspace 			= $settings[0]->photo_vspace;
	$hspace 			= $settings[0]->photo_hspace;
	$albVspace 			= $settings[0]->alb_vspace;
	$albHspace 			= $settings[0]->alb_hspace;
	$featVspace 		= $settings[0]->feat_vspace;
	$featHspace 		= $settings[0]->feat_hspace;
	
	$twidth 			= $settings[0]->thumbimg_width;
	$theight 			= $settings[0]->thumbimg_height;
	$fullImageWidth 	= $settings[0]->fullimg_width;
	$fullImageHeight 	= $settings[0]->fullimg_height;
	$albWidth 			= $settings[0]->alb_photo_width;
	$albHeight 			= $settings[0]->alb_photo_height;
	$featWidth 			= $settings[0]->feat_photo_width;
	$featHeight 		= $settings[0]->feat_photo_height;
}

$photoOrderNo 	= JRequest::getInt('photoid');
$leftArrow 		= $baseURL .'components/com_simplephotogallery/images/left-arrow.png';
$rightArrow 	= $baseURL .'components/com_simplephotogallery/images/right-arrow.png';

if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHtml::_('jquery.framework');
	JHtml::_('jquery.ui', array('core'));
}
else
{
?>
	<script type="text/javascript" src="<?php echo JURI::base().'components/com_simplephotogallery/js/zoominout/jquery.js'?>"></script>
	<script type="text/javascript" src="<?php echo JURI::base().'components/com_simplephotogallery/js/zoominout/jqueryui.js'?>"></script>
	
<?php
}
?>
<script type="text/javascript" src="<?php echo JURI::base().'components/com_simplephotogallery/js/pica_Carousel_with_autoscrolling_files/jquery.jcarousel.min.js';?>"></script>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo JURI::base().'components/com_simplephotogallery/css/pica_Carousel_with_autoscrolling_files/skin.css';?>" />
<html lang="en" class="ie8 ielt9"> <html lang="en">
<link rel="stylesheet" type="text/css" media="all" href="<?php echo JURI::base().'components/com_simplephotogallery/css/zoominout/iestyle.css';?>" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<script type="text/javascript"> 
 
function mycarousel_initCallback(carousel)
{
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
        carousel.startAuto(0);
    });
 
    carousel.buttonPrev.bind('click', function() {
        carousel.startAuto(0);
    });
 
    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
        carousel.stopAuto();
    }, function() {
        carousel.startAuto();
    });
};
var simplePhotoGallery = jQuery.noConflict();
simplePhotoGallery(document).ready(function() {
	simplePhotoGallery('#mycarouselPhoto').jcarousel({
        auto: 3,
        wrap: 'last',
        initCallback: mycarousel_initCallback,
        itemFallbackDimension: 300,
        visible:3
    });
});
 
</script>
<?php  
if($photoOrderNo <= 1 )	
{
	$leftArrowId = 0;
}
else
{
	$leftArrowId = $photoOrderNo - 1;
}

if($totalPhotos <= $photoOrderNo )
{
	$rightArrowId = 0;
}
else
{
	$rightArrowId = $photoOrderNo + 1;
}

if($photoID != '')
{
	$baseURI			= JFactory::getURI();
	
	$downloadID 		= $photoID;
	$downloadAlbumId 	= $albumID;
	
	$fbUrl  = 'http://www.facebook.com/dialog/feed?app_id='.$api_key.'&description='.urlencode(strip_tags($images[0]->description)).'&picture='.urlencode(JURI::root().'images/photogallery/thumb_image/'.trim($images[0]->image)).'&name='.$images[0]->title.'&message=Comments&link='.urlencode($baseURI->toString()).'&redirect_uri='.urlencode($baseURI->toString());
?>

	<style>
html.ielt9 #iviewer .viewer {
			display: block;
}
<!--
.fullscreecss {
border: 1px solid white;
float: left;
padding: 2px 5px;
}
.fullscreecss a {
font-size: 1.2em;
    text-decoration: none;
}
.fullscreecss span{font-family: Georgia,"Bitstream Charter",serif;}
.fullscreecss:hover {
border: 1px solid #CCC;
border-bottom-left-radius: 2px 2px;
border-bottom-right-radius: 2px 2px;
border-top-left-radius: 2px 2px;
border-top-right-radius: 2px 2px;
padding: 2px 5px;

}
.fullscreecss a:hover, .fullscreecss a:focus{background-color:#fff !important;color: black !important;}
-->

.fullImageView tr, td{border: none !important;}
</style>
	
	<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
	<div style="float:left;margin-right:10px;padding-bottom:10px;line-height: 15px;">
		<div class="fullscreecss">
			<a title="<?php echo  JText::_('SPG_FULL_SCREEN'); ?>" id='go' style="text-decoration: none;background-color:none !important;" href="<?php echo $baseURL . 'images/photogallery/'.$images[0]->image; ?>"> 
				<img src="<?php echo $baseURL . 'components/com_simplephotogallery/images/fullscreen.png';?>"/>
				<span> <?php echo  JText::_('SPG_FULL_SCREEN'); ?> </span>
			</a>
		</div>
	</div>

<?php if($isDownload == '1') { ?>		        
		<div style="float:left;margin-right:10px;padding-bottom:10px;line-height: 15px;" class="fullscreecss">
			<a title="<?php echo JText::_('SPG_DOWNLOAD') ?>" style="text-decoration: none;" href="<?php echo $baseURL . 'index.php?option=com_simplephotogallery&task=download&albumid='.$downloadAlbumId.'&photoid='.$downloadID; ?>">
				<img src="<?php echo $baseURL . 'components/com_simplephotogallery/images/download.png';?>"/>
				<span> <?php echo JText::_('SPG_DOWNLOAD') ?> </span>
			</a>
		</div>
<?php } 

	if($isShare == '1') { ?>
		<div style="float:left;line-height: 15px;padding-bottom:10px;">
			<div class="fullscreecss">
				<a  class="links" title="<?php echo JText::_('SPG_FBSHARE') ?>" href="javascript:void(0);" onClick='window.open("<?php echo $fbUrl; ?>","facebookShare","scrollbars=1,width=800,height=400")' style="text-decoration: none;" >
					<img src="<?php echo JURI::base().'components/com_simplephotogallery/images/facebook.gif';?>"/>
					<span> <?php echo JText::_('SPG_SHARE') ?> </span>
				</a>
			</div>
		</div>
<?php } ?>

	<div id="iviewer">
		<div class="loader"></div>
		<div class="viewer"></div>
		<ul class="controls">
			<li style="list-style: none;" title="<?php echo JText::_('SPG_CLOSE') ?>" class="close"></li>
			<li style="list-style: none;" title="<?php echo JText::_('SPG_ZOOMIN') ?>" class="zoomin"></li>
			<li style="list-style: none;" title="<?php echo JText::_('SPG_ZOOMOUT') ?>" class="zoomout"></li>
		</ul>
	</div>
	
		<script type="text/javascript" src="<?php echo JURI::base().'components/com_simplephotogallery/js/zoominout/jquery.iviewer.js'?>"></script>
		<script type="text/javascript" src="<?php echo JURI::base().'components/com_simplephotogallery/js/zoominout/main.js'?>"></script>
		<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo JURI::base().'components/com_simplephotogallery/css/zoominout/zoomstyle.css'?>" /> 
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo JURI::base().'components/com_simplephotogallery/css/zoominout/colorbox1.css'?>" />

	<div style="clear: both;"></div>

	<table class='fullImageView' id='fullImageView' width="100%" style="margin-bottom: 10px;">
		<tr>
			<td width="45px">
				<?php // Show left arrow 
				if($leftArrowId) {   
						$photoURL = JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=".JRequest::getVar('albumid')."&photoid=".($photoOrderNo-1)."#fullImageView");
				?>  	
						<img style="cursor: pointer;" src="<?php echo $leftArrow; ?>" onclick="showsliding('<?php echo $photoURL; ?>'); " />
				<?php } ?>
			</td>
			
			<td class="singlePhoto" style="text-align: center;">
				<img width='<?php echo $fullImageWidth; ?>' height='<?php echo $fullImageHeight; ?>' style="box-shadow:2px 2px 8px #888888" src="<?php echo $baseURL . 'images/photogallery/full_image/'.$images[0]->image; ?>">
			</td>
			
			<td width="45px">
				<?php // Show right arrow 
				if($rightArrowId) {
						$photoURL = JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=".JRequest::getVar('albumid')."&photoid=".($photoOrderNo+1)."#fullImageView");
				?>   
						<img style="cursor: pointer;" src="<?php echo $rightArrow; ?>" onclick="showsliding('<?php echo $photoURL;?>')"  />
				<?php } //if for photosno right arrow 
				?>	
			</td>
		</tr>
	</table>

		<div style="font-family: Georgia,Bitstream Charter,serif;font-size:16px;font-weight: bold;margin-top: 10px;margin-left: 75px;">
		<?php echo ucfirst($images[0]->title).':'; ?>
		</div>
		<div style="font-family: Georgia,Bitstream Charter,serif;font-size:15px;margin-top: 10px;margin-left: 75px;">
			<?php
			if($images[0]->description)
			{
				echo ucfirst($images[0]->description);
			}
			else
			{ 
				echo JText::_('SPG_NO_DESCRIPTION_AVAILABLE');
			}
			?>
		</div>

	<div style="height: 30px;"></div>
<?php 
}
else 
{
	$model 		= $this->getModel();
	$albumName 	= $model->getAlbumName($albumID);	
	
	if(count($images) > 0)
	{
?>
		<h1> <?php echo JText::_('SPG_PHOTOS_OF') . ' ' . $albumName[0]; ?></h1>
<?php 				echo $albumName[1];?>
		<div class="imageList">
<?php
		for($i = 0; $i < count($images); $i++)
		{
			$imageURL = JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=".$albumName[2]."&photoid=".$images[$i]->sortorder);
			?>
			<div style="cursor:pointer;width:<?php echo $twidth ?>px;height:<?php echo $theight+40; ?>px; float: left;box-shadow:2px 2px 8px #888888;margin: <?php echo $vspace.'px '.$hspace.'px' ?>;">
				<div onclick="showPhoto('<?php echo $imageURL?>')">
					<img title="<?php echo $images[$i]->title; ?>" alt="<?php echo $images[$i]->title; ?>" width="<?php echo $twidth?>" height="<?php echo $theight?>" src="<?php echo $baseURL . 'images/photogallery/thumb_image/'.$images[$i]->image; ?>"/>
					<div style="color: #888888;font-family: arial,sans-serif;font-size: 8pt;word-wrap: break-word;overflow: hidden;text-align: center;"><?php
					if (strlen($images[$i]->title) > 50) {
						$imageName = substr ( $images[$i]->title, 0, 50 ) . '..';
					} else {
						$imageName = $images[$i]->title;
					}
					echo $imageName; ?></div>
				</div>
			</div>
			<?php 
		}
?>		</div>
<?php }
	  else 
	  {
?>		<h1> <?php echo JText::_('SPG_PHOTOS'); ?> </h1>
<?php 		echo JText::_('SPG_NO_PHOTOS'); 
	  }
?>
		<div style="clear: both;height: 30px;"> </div>
<?php 
} ?>

<div style = 'clear:both;'></div>
<?php 
if(!JRequest::getVar('photoid'))
{
?>
	<style>
		.jcarousel-skin-tango1 .jcarousel-next-horizontal
		{
			top:<?php echo ($albHeight/2)+$albVspace?>px !important;
		}
		
		.jcarousel-skin-tango1 .jcarousel-prev-horizontal
		{
			top:<?php echo ($albHeight/2)+$albVspace?>px !important;
		}
	</style>
	
	<h1> <?php echo JText::_('SPG_ALBUMS'); ?> </h1>

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
	if(count($album) > 0)
	{ ?>
		<div id="wrap" style="width:<?php echo (3*$albWidth)+(6*$albHspace)+10;?>px">
			<ul id="mycarouselPhoto" class="jcarousel-skin-tango1" >
				<?php for($j = 0;$j < count($album); $j++) { ?>
						
							<li style="background:none !important;cursor:pointer;padding:0px !important;float:left;list-style:none outside none !important;margin:<?php echo $albVspace.'px '.$albHspace?>px">
						
						<?php $strUrl = 'index.php?option=com_simplephotogallery&view=images&albumid='.$album[$j]->id; ?>
								
								<a class="album-thumb" href="<?php echo JRoute::_($strUrl)?>" style=" padding-top: 1px;text-decoration:none;" >
								
								<?php if($album[$j]->image != '')
									{
										echo "<img style='box-shadow:1px 1px 2px #888888;' width='".$albWidth."px' height='".$albHeight."px' src='".JURI::base().'images/photogallery/album_image/'.$album[$j]->image."'>";
									}
									else
									{
										echo "<img width='".$albWidth."px' height='".$albHeight."px' src='".JURI::base()."components/com_simplephotogallery/images/default_star.gif'>";
									}
								?>
									<br> <b><?php echo $album[$j]->album_name ; ?></b>
								</a>
							</li>
				<?php } ?>			
			</ul>  
		</div>
	<?php
	}
	else{
		echo JText::_('SPG_NO_ALBUMS');
	} 
}
?>

<div style = 'clear:both;'></div>

<script type="text/javascript">
	function changepage(albumid)
	{  
	    window.location.href = '<?php echo JRoute::_("index.php?option=com_simplephotogallery&view=images&albumid=")?>'+albumid;
	}
	 function showsliding(photoURL)
	    {
	        
	        window.location.href = photoURL;
	    }
	 function showPhoto(imageURL)
	    {
	        window.location.href = imageURL;
	    }
</script>