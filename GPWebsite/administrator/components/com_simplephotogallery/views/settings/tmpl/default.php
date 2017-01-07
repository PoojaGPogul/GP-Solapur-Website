<?php
/**
 * Settings view file for Simple Photo Gallery
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
<style>
<!--
table.admintable td.key,table.admintable td.paramlist_key {
	background-color: #F6F6F6;
	border-bottom: 1px solid #E9E9E9;
	border-right: 1px solid #E9E9E9;
	color: #666666;
	font-weight: bold;
	text-align: right;
	width: 140px;
}

table.admintable td {
	padding: 3px;
}

fieldset input {
	float: none;
}
-->
</style>
<?php
$rows = $this->settings ['row'];

// If user clicks edit the following form is displayed
if (JRequest::getVar ( 'task' ) == 'edit') {
?>

<form action="<?php echo JRoute::_('index.php?view=settings'); ?>"
	method="post" name="adminForm" id="adminForm"
	enctype="multipart/form-data">
<?php
	for($i = 0, $n = count ( $rows ); $i < $n; $i ++) {
		$row = &$rows [$i];
		?>
                        <input type="checkbox" style="display: none;"
		checked="checked" value="1" name="cid[]" id="cb0" />
	<fieldset style="background-color: white; width: 40%; float: left"
		class="adminform">
		<legend>Featured Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">No of Columns</td>
					<td colspan="2"><input type="text" name="feat_cols" id="feat_cols"
						value="<?php echo $row->feat_cols; ?>" /></td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">No of Rows</td>
					<td colspan="2"><input type="text" name="feat_rows" id="feat_rows"
						value="<?php echo $row->feat_rows; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Width Of Photos(Px)</td>
					<td colspan="2"><input type="text" name="feat_photo_width"
						id="feat_photo_width"
						value="<?php echo $row->feat_photo_width; ?>" /></td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of Photos(Px)</td>
					<td colspan="2"><input type="text" name="feat_photo_height"
						id="feat_photo_height"
						value="<?php echo $row->feat_photo_height; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical Space(Px)</td>
					<td colspan="2"><input type="text" name="feat_vspace"
						id="feat_vspace" value="<?php echo $row->feat_vspace; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Horizontal Space(Px)</td>
					<td colspan="2"><input type="text" name="feat_hspace"
						id="feat_hspace" value="<?php echo $row->feat_hspace; ?>" /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<fieldset style="background-color: white; width: 45%; float: left"
		class="adminform">
		<legend>Photo Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">Width of
						thumbnail photos(Px)</td>
					<td colspan="2"><input type="text" name="thumbimg_width"
						id="thumbimg_width" value="<?php echo $row->thumbimg_width; ?>" />

					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">Height of thumbnail
						photos(Px)</td>
					<td colspan="2"><input type="text" name="thumbimg_height"
						id="thumbimg_height" value="<?php echo $row->thumbimg_height; ?>" />

					</td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical space(Px)</td>
					<td colspan="2"><input type="text" name="photo_vspace"
						id="photo_vspace" value="<?php echo $row->photo_vspace; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Horizontal space(Px)</td>
					<td colspan="2"><input type="text" name="photo_hspace"
						id="photo_hspace" value="<?php echo $row->photo_hspace; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Width Of large photos(Px)</td>
					<td colspan="2"><input type="text" name="fullimg_width"
						id="fullimg_width" value="<?php echo $row->fullimg_width; ?>" /></td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of large photos(Px)</td>
					<td colspan="2"><input type="text" name="fullimg_height"
						id="fullimg_height" value="<?php echo $row->fullimg_height; ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<div style="clear: both;"></div>
	<fieldset style="background-color: white; width: 40%; float: left"
		class="adminform">
		<legend>Albums Settings</legend>
		<table style="width: 100%" class="admintable">

			<tbody>
				<tr>
					<td width="20" class="key" style="width: 300px">Width Of Photos(Px)</td>
					<td colspan="2"><input type="text" name="alb_photo_width"
						id="alb_photo_width" value="<?php echo $row->alb_photo_width; ?>" />
						<br> <i>[Recommended width maximum 150px]</i></td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of Photos(Px)</td>
					<td colspan="2"><input type="text" name="alb_photo_height"
						id="alb_photo_height"
						value="<?php echo $row->alb_photo_height; ?>" /></td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical Space(Px)</td>
					<td colspan="2"><input type="text" name="alb_vspace"
						id="alb_vspace" value="<?php echo $row->alb_vspace; ?>" /></td>
				</tr>
				<tr>
					<td style="vertical-align: top;" width="20" class="key">Horizontal
						Space(Px)</td>
					<td colspan="2"><input type="text" name="alb_hspace"
						id="alb_hspace" value="<?php echo $row->alb_hspace; ?>" /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<fieldset style="background-color: white; width: 45%; float: left"
		class="adminform">
		<legend>General Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">Share Photos</td>
					<td colspan="2"><input id="general_share_photo" type="radio"
						<?php if($row->general_share_photo == '1'){echo 'checked=checked';}?>
						name="general_share_photo" value="1"> Enable &nbsp;&nbsp; <input
						id="general_share_photo" type="radio" name="general_share_photo"
						<?php if($row->general_share_photo == '0'){echo 'checked=checked';}?>
						value="0"> Disable</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">Downlaod Option</td>
					<td colspan="2"><input id="general_download" type="radio"
						<?php if($row->general_download == '1'){echo 'checked=checked';}?>
						name="general_download" value="1"> Enable &nbsp;&nbsp; <input
						id="general_download" type="radio" name="general_download"
						<?php if($row->general_download == '0'){echo 'checked=checked';}?>
						value="0"> Disable</td>
				</tr>
				<tr>
					<td width="20" class="key">Show Albums in Featured Page</td>
					<td colspan="2"><input id="general_show_alb" type="radio"
						<?php if($row->general_show_alb == '1'){echo 'checked=checked';}?>
						name="general_show_alb" value="1"> Enable &nbsp;&nbsp; <input
						id="general_show_alb" type="radio" name="general_show_alb"
						<?php if($row->general_show_alb == '0'){echo 'checked=checked';}?>
						value="0"> Disable</td>
				</tr>
				
				<tr>
					<td width="20" class="key">Facebook API key</td>
					<td colspan="2"><input type="text" name="facebook_api_id"
						id="facebook_api_id" value="<?php if(isset($row->facebook_api_id)) echo $row->facebook_api_id; ?>" />
						</td>
				</tr>


			</tbody>
		</table>
	</fieldset>
          <?php }?>
          <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
	<input type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="task" value="edit" /> <input type="hidden"
		name="controller" value="settings" /> <input type="hidden"
		name="boxchecked" value="1" />
</form>
<?php
} else {
	?>
<form action="<?php echo JRoute::_('index.php?view=settings'); ?>"
	method="post" name="adminForm" id="adminForm"
	enctype="multipart/form-data">
					<?php
	for($i = 0, $n = count ( $rows ); $i < $n; $i ++) {
		$row = &$rows [$i];
		?>
                        <input type="checkbox" style="display: none;"
		checked="checked" value="1" name="cid[]" id="cb0" />
	<fieldset style="background-color: white; width: 40%; float: left"
		class="adminform">
		<legend>Featured Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">No of Columns</td>
					<td colspan="2">
                            <?php echo $row->feat_cols; ?>
                            </td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">No of Rows</td>
					<td colspan="2">
                             <?php echo $row->feat_rows; ?>
                             </td>
				</tr>
				<tr>
					<td width="20" class="key">Width Of Photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->feat_photo_width; ?>
                            </td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of Photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->feat_photo_height; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical Space(Px)</td>
					<td colspan="2">
                            <?php echo $row->feat_vspace; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Horizontal Space(Px)</td>
					<td colspan="2">
                            <?php echo $row->feat_hspace; ?>
                            </td>
				</tr>


			</tbody>
		</table>
	</fieldset>
	<fieldset style="background-color: white; width: 45%; float: left"
		class="adminform">
		<legend>Photo Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">Width of
						thumbnail photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->thumbimg_width; ?>
                            </td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">Height of thumbnail
						photos(Px)</td>
					<td colspan="2">
                             <?php echo $row->thumbimg_height; ?>
                             </td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical space(Px)</td>
					<td colspan="2">
                            <?php echo $row->photo_vspace; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Horizontal space(Px)</td>
					<td colspan="2">
                            <?php echo $row->photo_hspace; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Width Of large photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->fullimg_width; ?>
                            </td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of large photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->fullimg_height; ?>
                            </td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<div style="clear: both;"></div>
	<fieldset style="background-color: white; width: 40%; float: left"
		class="adminform">
		<legend>Albums Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td width="20" class="key" style="width: 300px">Width Of Photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->alb_photo_width; ?>
                            </td>
				</tr>

				<tr>
					<td width="20" class="key">Height Of Photos(Px)</td>
					<td colspan="2">
                            <?php echo $row->alb_photo_height; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Vertical Space(Px)</td>
					<td colspan="2">
                            <?php echo $row->alb_vspace; ?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Horizontal Space(Px)</td>
					<td colspan="2">
                            <?php echo $row->alb_hspace; ?>
                            </td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<fieldset style="background-color: white; width: 45%; float: left"
		class="adminform">
		<legend>General Settings</legend>
		<table style="width: 100%" class="admintable">
			<tbody>
				<tr>
					<td align="right" class="key" style="width: 300px">Share Photos</td>
					<td colspan="2">
                            <?php
		if ($row->general_share_photo == '1') {
			echo "Enabled";
		} else {
			echo "Disabled";
		}
		?>
                            </td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">Downlaod Option</td>
					<td colspan="2">
                             <?php
		if ($row->general_download == '1') {
			echo "Enabled";
		} else {
			echo "Disabled";
		}
		?>
                             </td>
				</tr>
				<tr>
					<td width="20" class="key">Show Albums in Featured Page</td>
					<td colspan="2">
                            
                            <?php
		if ($row->general_show_alb == '1') {
			echo "Enabled";
		} else {
			echo "Disabled";
		}
		?>
                            </td>
				</tr>
				<tr>
					<td width="20" class="key">Facebook API Key</td>
					<td colspan="2">
                            
                            <?php
		if (!empty($row->facebook_api_id)) {
			echo $row->facebook_api_id;
		} else {
			echo "";
		}
		?>
                            </td>
				</tr>
			</tbody>
		</table>
	</fieldset>
          <?php }?>
          <input type="hidden" name="option"
		value="com_simplephotogallery" /> <input type="hidden" name="task"
		value="edit" /> <input type="hidden" name="controller"
		value="settings" /> <input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="" /> <input
		type="hidden" name="filter_order_Dir" value="" />
</form>

<script type="text/javascript">
            var ch = document.getElementsByName("cid[]");
            isChecked(ch[0].checked);
        </script>
<?php } ?>

<script type="text/javascript">
Joomla.submitbutton = function(pressbutton)
{
    	if(pressbutton == "regenrate"){
    		 var confirm=window.confirm("<?php
							
echo JText::_ ( 'Are you sure want to re create image based on gallery settings?' ) . '\nAlbum image size:' . $row->alb_photo_width . 'px X ' . $row->alb_photo_height . 'px' . '\nFeatured photo size:' . $row->feat_photo_width . 'px X ' . $row->feat_photo_height . 'px' . '\nThumb image size:' . $row->thumbimg_width . 'px X ' . $row->thumbimg_height . 'px' . '\nFull image size:' . $row->fullimg_width . 'px X ' . $row->fullimg_height . 'px';
							?>")
			 if (!confirm)
			 return;
        }
        
        submitform( pressbutton );
        return;
    }


// validation for 1.5 
function submitbutton(pressbutton)
{
    	if(pressbutton == "regenrate"){
            var confirm=window.confirm("<?php
												
echo JText::_ ( 'Are you sure want to re create image based on gallery settings?' ) . '\nAlbum image size:' . $row->alb_photo_width . 'px X ' . $row->alb_photo_height . 'px' . '\nFeatured photo size:' . $row->feat_photo_width . 'px X ' . $row->feat_photo_height . 'px' . '\nThumb image size:' . $row->thumbimg_width . 'px X ' . $row->thumbimg_height . 'px' . '\nFull image size:' . $row->fullimg_width . 'px X ' . $row->fullimg_height . 'px';
												?>")
            if (!confirm)
            return;
        }
        

        submitform( pressbutton );
        return;
    }
</script>
