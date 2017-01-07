<?php
/**
 * Media view file for Simple Photo Gallery
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
?>
<fieldset>
	<div class="fltlft">
		<h3><?php echo JText::_("Folder Name") ?> : <?php echo ucfirst($this->folder); ?></h3>
	</div>
	<div class="fltrt">
		<input type="button" id="insertURL" value="Insert"
			onclick="window.parent.jInsertFieldValue(document.getElementById('f_url').value,'<?php echo JRequest::getCmd("fieldid"); ?>');window.parent.SqueezeBox.close();" />
		<input type="button" id="cancelURL" value="Cancel"
			onclick="window.parent.SqueezeBox.close();" />
	</div>
</fieldset>
<fieldset>
	<legend><?php echo JText::_("Choose file") ?></legend>
<?php if (count($this->list["images"]) > 0 || count($this->list["folders"]) > 0) { ?>
<div class="manager">

		<?php
	
for($i = 0, $n = count ( $this->list ["images"] ); $i < $n; $i ++) :
		$row = &$this->list ["images"] [$i];
if (strlen ($row->name) > 15) {
	$imageName = substr ( $row->name, 0, 15 ) . '..';
} else {
	$imageName = $row->name;
}
		?>
                    <div class="item">
			<a title="<?php echo "images/$this->folder/".$row->name; ?>"
				href="javascript:void(0);"
				onclick="document.getElementById('f_url').value= this.title; ">
                                <?php if($row->mime == "image/jpeg" || $row->mime == "image/png" || $row->mime == "image/gif" || $row->mime == "image/bmp"){ ?>
                                            <img
				width="<?php echo $row->width_60; ?>"
				height="<?php echo $row->height_60; ?>"
				alt="<?php echo $row->name; ?> - <?php echo $row->size; ?>"
				src="<?php echo JURI::root()."images/$this->folder/".$row->name; ?>">
                                <?php
		
} else {
			?>
                                            <img
				src="<?php echo JURI::root()."media/media/images/con_info.png" ?>" />
                                            <?php } ?>

                                            <span
				title="<?php echo $row->name; ?>"><?php echo $imageName; ?></span>
			</a>
		</div>
		 <?php endfor; ?>

</div>
<?php } else { ?>
	<div id="media-noimages">
		<p><?php echo JText::_('COM_MACGALLERY_NO_FILES_FOUND'); ?></p>
	</div>
<?php } ?>
</fieldset>
<fieldset>
	<table class="properties">
		<tbody>
			<tr>
				<td><label for="f_url"><?php echo JText::_('Photo gallery image URL'); ?></label></td>
				<td><input style="width: 300px;" type="text" value="" id="f_url"></td>
			</tr>
		</tbody>
	</table>
</fieldset>
<form
	action="<?php echo JURI::base(); ?>index.php?option=com_simplephotogallery&view=images&task=photo_upload"
	id="adminform" name="adminform" method="post"
	enctype="multipart/form-data">
	<fieldset id="uploadform">
		<legend><?php echo JText::_('Upload maximum 10 MB'); ?></legend>
		<fieldset id="upload-noflash" class="actions">
			<label for="upload-file" class="hidelabeltxt"><?php echo JText::_('Upload file'); ?></label>
			<input type="file" id="upload-file" name="upload-file" /> <input
				type="submit" id="upload-submit"
				value="<?php echo JText::_('Start upload'); ?>" />
		</fieldset>
		<ul class="upload-queue" id="upload-queue">
			<li style="display: none"></li>
		</ul>
		<input type="hidden" name="option" value="com_simplephotogallery" /> <input
			type="hidden" name="task" value="photo_upload" /> <input
			type="hidden" name="controller" value="images" /> <input
			type="hidden" name="return-url"
			value="<?php echo base64_encode('index.php?option=com_simplephotogallery&view=media&tmpl=component&fieldid='.JRequest::getCmd("fieldid").'&asset=&author=&folder='.$this->folder); ?>" />
	</fieldset>
</form>

