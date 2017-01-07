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

// No direct acesss
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Import filesystem libraries.
JHtml::_ ( 'behavior.tooltip' );
jimport ( 'joomla.filesystem.file' );

$document = JFactory::getDocument ();
?>

<script language="JavaScript" type="text/javascript">
	
	function submitbutton(pressbutton) {

        var form=document.adminForm;

        if(pressbutton=="saveclose" || pressbutton=="apply") {

            if (form.title.value == '') {
                alert("<?php  echo JText::_('Please enter image name') ?>");
                document.getElementById('title').focus();
                
                return false;
            }

            if (form.title.value != "") {

            	if(!isAliasName(form.title.value)){

                	alert("<?php  echo JText::_('Special characters are not allowed in image name') ?>");
                    document.getElementById('title').focus();
                    
                    return false;
            	}
        	}

            if (form.alias_name.value != "") {
            
            	if(!isAliasName(form.alias_name.value)){

	            	alert("<?php  echo JText::_('Special characters are not allowed in alias name') ?>");
	                document.getElementById('alias_name').focus();
	                
	                return false;
        		}
            }
        }
        submitform( pressbutton );
        
        return;
    }

	Joomla.submitbutton = function(pressbutton) {
		
        var form=document.adminForm;
        
        if(pressbutton=="saveclose" || pressbutton=="apply") {

            if (form.title.value == '') {
                
                alert("<?php  echo JText::_('Please enter image name') ?>");
                document.getElementById('title').focus();
                
                return false;
            } 

            if (form.title.value != "") {

            	if(!isAliasName(form.title.value)){

                	alert("<?php  echo JText::_('Special characters are not allowed in image name') ?>");
                    document.getElementById('title').focus();
                    
                    return false;
            	}
            }

            if (form.alias_name.value != "") {
            
	            if(!isAliasName(form.alias_name.value)){
		            
	            	alert("<?php  echo JText::_('Special characters are not allowed in alias name') ?>");
	                document.getElementById('alias_name').focus();
	                
	                return false;
	        	}
            }
        }
        submitform( pressbutton );
        
        return;
    }

	function isAliasName(aliasName) {
	 	var check = /^([a-zA-Z0-9\-\_\ ]+)$/;
	 	
	 	return check.test(aliasName);
	}
</script>
<?php
$k = 0;
$baseUrl = JURI::base ();
$compDirRoot = JPATH_ROOT;
$siteUrl = JPATH_SITE;

$editor = JFactory::getEditor ();

$path = $baseUrl . "components/com_simplephotogallery";

$folder = $compDirRoot . DS . "images" . DS . "photogallery" . DS;
$fullimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "full_image";
$featuredimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "featured_image";
$albumimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "album_image";
$thumbimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "thumb_image";

if (! is_dir ( $folder )) {
	mkdir ( $folder );
}
if (! is_dir ( $fullimgfolder )) {
	mkdir ( $fullimgfolder );
}
if (! is_dir ( $featuredimgfolder )) {
	mkdir ( $featuredimgfolder );
}
if (! is_dir ( $albumimgfolder )) {
	mkdir ( $albumimgfolder );
}
if (! is_dir ( $thumbimgfolder )) {
	mkdir ( $thumbimgfolder );
}

$src = $siteUrl . "/components/com_simplephotogallery/secure/";
$dst = $siteUrl . "/images/photogallery/";
$fullimgfolder = $siteUrl . "/images/photogallery/full_image/";
$featuredimgfolder = $siteUrl . "/images/photogallery/featured_image/";
$albumimgfolder = $siteUrl . "/images/photogallery/album_image/";
$thumbimgfolder = $siteUrl . "/images/photogallery/thumb_image/";

$file = '.htaccess';
if (file_exists ( $src . $file )) {
	@copy ( $src . $file, $dst . $file );
	@copy ( $src . $file, $fullimgfolder . $file );
	@copy ( $src . $file, $featuredimgfolder . $file );
	@copy ( $src . $file, $albumimgfolder . $file );
	@copy ( $src . $file, $thumbimgfolder . $file );
}

if (file_exists ( $src . $file )) {
	JFile::delete ( $src . $file );
	rmdir ( $src );
}

if (version_compare ( JVERSION, '3.0.0', 'ge' )) {
	JHtml::_ ( 'jquery.ui', array (
			'core',
			'sortable' 
	) );
} else {
	$document->addScript ( $path . '/js/jquery-1.3.2.min.js' );
	$document->addScript ( $path . '/js/jquery-ui-1.7.1.custom.min.js' );
}
?>

<script type="text/javascript">
    // When the document is ready set up our sortable with it's inherant function(s)
    var dragdr = jQuery.noConflict();
    var videoid = new Array();
    
    dragdr(document).ready(function() {
        dragdr("#test-list").sortable({
            handle : '.handle',
            update : function () {
                var order = dragdr('#test-list').sortable('serialize');

                orderid = order.split("listItem[]=");

                for(i = 1;i < orderid.length;i++)
                {
                    videoid[i] = orderid[i].replace('&',"");
                    oid = "ordertd_"+videoid[i];
                    document.getElementById(oid).innerHTML = i-1;
                }
                dragdr("#info").load("<?php echo $baseUrl; ?>/index.php?option=com_simplephotogallery&task=sortorder&"+order);
           }
        });
        dragdr(".mceEditor").after("<br/>");
    });

</script>

<?php

$albumval = $this->images ['albumval'];
$pageval = $this->images;
$albumtot = count ( $albumval );

if (JRequest::getVar ( 'task' ) == 'edit' || JRequest::getVar ( 'task' ) == '') {
	$rows = $this->images ['row'];
	$lists = $this->images ['lists'];
}

if (! isset ( $option )) {
	$option = '';
}

if (JRequest::getVar ( 'task' ) == 'edit' || JRequest::getVar ( 'task' ) == 'add') {
	
	$task_new = JRequest::getVar ( 'task' );
	
	if (JRequest::getVar ( 'task' ) == 'edit') {
		?>
<form action="index.php?option=com_simplephotogallery&view=images"
	method="post" name="adminForm" id="adminForm"
	enctype="multipart/form-data">
	<fieldset class="adminform" style="background-color: white">
		<legend><?php  echo JText::_('Images') ?></legend>
		<input type="hidden" value="<?php echo $task_new ?>" id="hdntask" />
		<table class="admintable" id="upladtable" style="width: 60%">

			<tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Title') ?>::<?php  echo JText::_('Title') ?>"><?php  echo JText::_('Title') ?><font
						color="red">*</font></span></td>
				<td><input style="width: 380px" type="text" name="title" id="title"
					value="<?php if ($task_new == 'edit') {echo $rows->title;} ?>"></td>
			</tr>
			<tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Alias Name') ?>::<?php  echo JText::_('Alias Name') ?>"><?php  echo JText::_('Alias Name') ?></span></td>
				<td><input style="width: 380px" type="text" name="alias_name"
					id="alias_name"
					value="<?php if ($task_new == 'edit') {echo $rows->alias_name;} ?>"></td>
			</tr>
			<tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Description') ?>::<?php  echo JText::_('Description') ?>"><?php  echo JText::_('Description') ?></span></td>
				<td>
	                    <?php
		
		$editor = JFactory::getEditor ();
		$imageDesc = "";
		if (isset ( $rows->description ))
			$imageDesc = $rows->description;
		?>
                                <textarea style="width: 380px"
						name="description" id="description" rows="3"><?php echo $imageDesc; ?></textarea>
					<!--  <input  type="text" name="description[]" id="description" value="<?php // if ($task_new == 'edit') {echo $rows->description;} ?>">-->
				</td>
			</tr>
			<tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Select Image') ?>::<?php  echo JText::_('Select Image') ?>"><?php  echo JText::_('Select Image') ?><font
						color="red">*</font></span></td>
				<td>
	                    <?php
		
		$folder = $compDirRoot . DS . "images" . DS . "photogallery" . DS;
		$fullimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "full_image";
		$featuredimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "featured_image";
		$albumimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "album_image";
		$thumbimgfolder = $compDirRoot . DS . "images" . DS . "photogallery" . DS . "thumb_image";
		
		if (! is_dir ( $folder )) {
			mkdir ( $folder );
		}
		if (! is_dir ( $fullimgfolder )) {
			mkdir ( $fullimgfolder );
		}
		if (! is_dir ( $featuredimgfolder )) {
			mkdir ( $featuredimgfolder );
		}
		if (! is_dir ( $albumimgfolder )) {
			mkdir ( $albumimgfolder );
		}
		if (! is_dir ( $thumbimgfolder )) {
			mkdir ( $thumbimgfolder );
		}
		
		if (! file_exists ( $compDirRoot . DS . "images" . DS . "photogallery" . DS . "index.html" )) {
			
			$fp = fopen ( $compDirRoot . DS . "images" . DS . "photogallery" . DS . "index.html", "w" );
			$content = "<html><body></body></html>";
			fwrite ( $fp, $content, strlen ( $content ) );
			fclose ( $fp );
		}
		
		$folder = "photogallery";
		JHtml::_ ( 'behavior.modal' );
		
		// Build the script.
		$script = array ();
		$script [] = '	function jInsertFieldValue(value, id) {';
		$script [] = '		var old_id = document.getElementById(id).value;';
		$script [] = '		if (old_id != id) {';
		$script [] = '			var elem = document.getElementById(id)';
		$script [] = '			elem.value = value;';
		$script [] = '			elem.fireEvent("change");';
		$script [] = '		}';
		$script [] = '	}';
		
		// Add the script to the document head.
		JFactory::getDocument ()->addScriptDeclaration ( implode ( "\n", $script ) );
		
		// Initialize some field attributes.
		$html = array ();
		$attr = '';
		
		$attr .= ' class="inputbox"';
		$attr .= ' size="40"';
		$this->value = isset ( $this->value ) ? $this->value : '';
		
		// Initialize JavaScript field attributes.
		$html [] = '<div class="fltlft" style="float:left">';
		$html [] = '	<input type="text" name="image" id="myfile"';
		
		if (isset ( $this->images ['row']->image )) {
			$html [] = ' value="' . "images/photogallery/" . $this->images ['row']->image . '"';
		}
		
		$html [] = ' readonly="readonly"' . $attr . ' />';
		$html [] = '</div>';
		$html [] = '<div class="button2-left" style="float:left;margin-left:5px !important; margin-top:0px !important">';
		$html [] = '	<div class="blank">';
		$html [] = '<a class="btn btn-primary modal" title="select" href="' . JURI::base () . 'index.php?option=com_simplephotogallery&view=media&tmpl=component&fieldid=myfile&folder=' . $folder . '" rel="{handler:\'iframe\', size: {x: 800, y: 500}}">';
		$html [] = '			' . JText::_ ( 'select' ) . '</a>';
		$html [] = '	</div>';
		$html [] = '</div>';
		$html [] = '<div class="button2-left" style="float:left;margin-left:5px !important; margin-top:0px !important">';
		$html [] = '	<div class="blank">';
		$html [] = '		<a class="btn btn-primary" title="' . JText::_ ( 'clear' ) . '"' . ' href="javascript:void(0);"' . ' onclick="document.getElementById(\'myfile\').value=\'\'; document.getElementById(\'myfile\').onchange();">';
		$html [] = '			' . JText::_ ( 'clear' ) . '</a>';
		$html [] = '	</div>';
		$html [] = '</div>';
		
		echo implode ( "\n", $html );
		?>
				</td>
			</tr>
                    <?php if(JRequest::getVar('task') == 'edit' && $this->images['row']->image !="") :?>
                    <tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Image Preview') ?>::<?php  echo JText::_('Image Preview') ?>"><?php  echo JText::_('Image Preview') ?></span></td>
				<td>
                    	<?php if(isset($this->images['row']->image)): ?>
                    		<img height="50" width="50"
					src="<?php echo JURI::root()."images/photogallery/featured_image/".$this->images['row']->image; ?>" />
                    	<?php endif; ?>
                    	</td>
			</tr>
                    <?php endif; ?>
                    <tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Album Name') ?>::<?php  echo JText::_('Album Name') ?>">
	                    <?php  echo JText::_('Album Name') ?><font
						color="red">*</font>

				</span></td>
				<td id="selectalbum"><select id="album_id" name="album_id"
					style="width: 100px">
						<option value="0">--<?php  echo JText::_('Select') ?>--</option>
	                        <?php for ($i = 0; $i < $albumtot; $i++) { ?>
	                            <option
							value="<?php echo $albumval[$i]->id; ?>"
							<?php if ($task_new == 'edit') {if ($rows->album_id == $albumval[$i]->id) {echo "selected='selected'";}}   else{  echo (JRequest::getInt("albumid") == $albumval[$i]->id)? "selected=selected ":"";          }  ?>>
	                            <?php echo $albumval[$i]->album_name; ?>
	                            </option>
	                        <?php } ?>
	                        </select></td>
			</tr>
			<tr>
				<td class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Published') ?>::<?php  echo JText::_('Published') ?>"><?php  echo JText::_('Published') ?></span></td>
				<td><input type="radio" style="float: left" name="published"
					<?php
		
		if ($task_new == 'edit') {
			if ($this->images ['row']->published == 1) {
				echo 'checked="checked" ';
			}
		} else {
			echo "checked='checked'";
		}
		?>
					value="1" /> <span style="float: left;">&nbsp;<?php  echo JText::_('Yes') ?>&nbsp;&nbsp;</span>


					<input type="radio" style="float: left" name="published"
					<?php
		
		if ($task_new == 'edit') {
			if ($this->images ['row']->published == 0) {
				echo 'checked="checked" ';
			}
		}
		?>
					value="0" /> <span style="float: left;">&nbsp;<?php  echo JText::_('No') ?></span>

				</td>
			</tr>
		</table>
	</fieldset>

	<input type="hidden" name="id" id="id"
		value="<?php if ($task_new == 'edit') {echo $rows->id;} ?>" /> <input
		type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="controller" value="images" /> <input type="hidden"
		name="task" value="" /> <input type="hidden" name="boxchecked"
		value="1" /> <input type="hidden" name="uploadfiles" id="uploadfiles"
		value="" />
</form>

<?php
	} else {
		
		$setting = $this->settings ["row"] [0];
		$tw = $setting->thumbimg_width;
		$th = $setting->thumbimg_height;
		$aw = $setting->alb_photo_width;
		$ah = $setting->alb_photo_height;
		$featw = $setting->feat_photo_width;
		$feath = $setting->feat_photo_height;
		$fw = $setting->fullimg_width;
		$fh = $setting->fullimg_height;
		?>

<script type="text/javascript"
	src="<?php echo $path . '/js/swfupload/swfupload.js'; ?>"></script>
<script type="text/javascript"
	src="<?php echo $path . '/js/jquery.swfupload.js'; ?>"></script>

<form action="index.php?option=com_simplephotogallery&view=images"
	method="post" name="adminForm" id="adminForm"
	enctype="multipart/form-data">

	<div id="albumSelect">
		Please select the album first to upload images: <select name="albumid"
			style="width: 100px" onchange="showUploader(this.value)">
			<option value="0">--<?php  echo JText::_('Select') ?>--</option>
		        <?php for ($i = 0; $i < $albumtot; $i++) { ?>
		        	<option value="<?php echo $albumval[$i]->id; ?>"
				<?php if(JRequest::getInt("albumid")==$albumval[$i]->id) echo "selected='selected' " ?>>
		        <?php echo $albumval[$i]->album_name; ?>
		        </option>
		        <?php } ?>
			</select>
	</div>

	<div id="swfupload-control"
		<?php if(JRequest::getInt("albumid")=="") echo "style='display:none'";  ?>>
		<p>Upload upto 20 image files(jpg, png, gif), each having maximum size
			of 1MB</p>
		<input type="button" id="button" />
		<p id="queuestatus"></p>
		<ol id="log"></ol>
	</div>

	<input type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="controller" value="images" /> <input type="hidden"
		name="task" value="" /> <input type="hidden" name="boxchecked"
		value="1" />
</form>
<?php
		$user = JFactory::getUser ();
		
		// base path
		$this->comPath = JURI::base ();
		
		// path for upload php
		$filepath = urlencode ( $siteUrl . DS . 'images' . DS . "photogallery" . DS );
		$this->uploadfilePath = $this->comPath . 'components/com_simplephotogallery/lib/uploadFile.php?jpath=' . $filepath . '&th=' . $th . '&tw=' . $tw . '&ah=' . $ah . '&aw=' . $aw . '&fh=' . $fh . '&fw=' . $fw . '&feath=' . $feath . '&featw=' . $featw;
		
		?>

<script type="text/javascript">
		var totalQueues;
		
		jQuery(function(){
			
			jQuery('#swfupload-control').swfupload({
					upload_url: '<?php echo $this->uploadfilePath; ?>',
					file_post_name: 'uploadfile',
					file_size_limit : "1024",
					file_types : "*.jpg;*.png;*.gif",
					file_types_description : "Image files",
					file_upload_limit : 20,
					flash_url : "<?php echo $path ?>/js/swfupload/swfupload.swf",
					button_image_url : '<?php echo $path ?>/js/swfupload/wdp_buttons_upload_114x29.png',
					button_width : 114,
					button_height : 29,
					"<?php echo $user->name; ?>":"<?php echo $user->id; ?>",
					button_placeholder : jQuery('#button')[0],
					debug: false
				})
				
				.bind('fileQueued', function(event, file){
					var listitem='<li id="'+file.id+'" >'+
						'File: <em>'+file.name+'</em> ('+Math.round(file.size/1024)+' KB) <span class="progressvalue" ></span>'+
						'<div class="progressbar" ><div class="progress" ></div></div>'+
						'<p class="status" >Pending</p>'+
						'<span class="cancel" >&nbsp;</span>'+
						'</li>';
					jQuery('#log').append(listitem);

					jQuery('li#'+file.id+' .cancel').bind('click', function(){
						var swfu = jQuery.swfupload.getInstance('#swfupload-control');
						swfu.cancelUpload(file.id);
						jQuery('li#'+file.id).slideUp('fast');
					});

					// start the upload since it's queued
					jQuery(this).swfupload('startUpload');
				})
				
				.bind('fileQueueError', function(event, file, errorCode, message){
					alert('Size of the file '+file.name+' is greater than limit');
				})
				
				.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){

					totalQueues  = numFilesQueued;
					jQuery('#queuestatus').text('Files Selected: '+numFilesSelected+' / Queued Files: '+numFilesQueued);
				})
				
				.bind('uploadStart', function(event, file){
					jQuery('#log li#'+file.id).find('p.status').text('Uploading...');
					jQuery('#log li#'+file.id).find('span.progressvalue').text('0%');
					jQuery('#log li#'+file.id).find('span.cancel').hide();
				})
				
				//Show Progress
				.bind('uploadProgress', function(event, file, bytesLoaded){				
					var percentage=Math.round((bytesLoaded/file.size)*100);
					jQuery('#log li#'+file.id).find('div.progress').css('width', percentage+'%');
					jQuery('#log li#'+file.id).find('span.progressvalue').text(percentage+'%');
				})
				
				.bind('uploadSuccess', function(event, file, serverData){
					appendHtmlfile(serverData,file);	
									
					var item=jQuery('#log li#'+file.id);
					item.find('div.progress').css('width', '100%');
					item.find('span.progressvalue').text('100%');

					var pathtofile='<a href="<?php echo JURI::root()."images/photogallery"; ?>/'+file.name+'" target="_blank" >view &raquo;</a>';
					item.addClass('success').find('p.status').html('Done!!! ');					
				})
				
				.bind('uploadComplete', function(event, file){				
					// upload has completed, try the next one in the queue
					jQuery(this).swfupload('startUpload');
				})
			
				});	
		var fileCount = 0;
		function appendHtmlfile(serverData,file){
			var filename =  file.name.split(".");
			var html = "<fieldset id='"+file.id+fileCount+"' ><legend>"+file.name+"</legend><div align='right' style='cursor:pointer' onclick=\"removeFieldSet('"+file.id+fileCount+"')\"><img title='Remove'  style='float:right' width='16' height='16' src='<?php echo JURI::base()."components/com_simplephotogallery/js/swfupload/cancel.png" ?>' /></div><table><tr><td valign='middle' style='width:100px'>Image Name</td><td valign='top'><input type='text' style='width:200px;' name='title[]' value='"+filename[0]+"'  /> <input type='hidden' name='image[]' value='"+serverData+"'  /></td></tr>";
			html += "<tr><td valign='middle' style='width:100px'>Alias Name</td><td valign='top'><input type='text' style='width:200px;' name='alias_name[]' id='alias_name[]'/></td></tr>";
			html += "<tr><td valign='middle' style='width:100px'>Description</td><td valign='top'><textarea name='imagedesc[]' style='height:50px;font-size:12px;width:200px'></textarea></td>";
			html += "<td><img height='50' width='50' src='<?php echo JURI::root()."images/photogallery/featured_image/"; ?>"+serverData+"' /></td></tr>";
			html += "</table></fieldset>"; 
			jQuery("#swfupload-control").append(html);
			fileCount ++;
		}
		function showUploader(value){
			if(value!="0"){
				jQuery("#swfupload-control").show();
			}
			else{
				jQuery("#swfupload-control").hide();
			}
		}
		function removeFieldSet(id){
			jQuery("#"+id).remove();
		}
		
		</script>
<?php
	}
} else {
	
	$albumId = JREQUEST::getVar ( 'albumid' );
	
	if ($albumId != '') {
		$albumValue = '&albumid=' . $albumId;
	} else {
		$albumValue = '';
	}
	
	$mainframe = JFactory::getApplication ();
	$search = $mainframe->getUserStateFromRequest ( $option . 'search', 'search', '', 'string' );
	?>

<form
	action="<?php echo JRoute::_('index.php?option=com_simplephotogallery&view=images').$albumValue; ?>"
	method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td align="left" width="100%">
                   <?php echo JText::_( 'Search' ); ?> :
                    <input type="text" name="search" id="search"
				value="<?php if ($search) echo $search;?>" class="text_area"
				onchange="document.adminForm.submit();" />
				<button onClick="this.form.submit();" style="margin-bottom: 9px;"><?php echo JText::_( 'Go' ); ?></button>
				<button onClick="document.getElementById('search').value='';" style="margin-bottom: 9px;"><?php  echo JText::_( 'Reset' ); ?></button>

			</td>
			<td nowrap="nowrap"><?php echo JText::_('Select a Album') ?>&nbsp;</td>
			<td align="right">
                        <?php
	$albumid = "";
	$albumid = JRequest::getVar ( 'albumid', '', 'get', 'var' );
	?>
                        <select id="albumid" name="albumid"
				onchange="select_albumname()">
                        <?php for ($i = 0; $i < $albumtot; $i++) { ?>
                            <option
						value="<?php echo $albumval[$i]->id; ?>"
						<?php
		if ($albumid == $albumval [$i]->id) {
			echo "selected='selected'";
		}
		?>>
                            <?php
		$albumval [$i]->album_name = isset ( $albumval [$i]->album_name ) ? $albumval [$i]->album_name : '';
		echo $albumval [$i]->album_name;
		?>
                            </option>
                        <?php } ?>
                        </select>
			</td>
		</tr>
		<tr height="5px;">
			<td colspan="2"></td>
		</tr>
	</table>
	<?php
	if (empty ( $rows )) {
		?>
<div class="alert alert-no-items">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS') ?>		</div>
<?php
	} else {
		?>
	<table class="adminlist table table-striped"
		style="position: relative;">
		<thead>
			<tr>
				<th width="1%" class="hidden-phone"><input type="checkbox"
					name="toggle" value=""
					onclick="
					<?php if (!version_compare(JVERSION, '3.0.0', 'ge')){?>
					checkAll(<?php echo count($rows); ?>);
					<?php }	else { ?>
						Joomla.checkAll(this);
					<?php } ?>
					" /></th>
				<th width="2%" class="hidden-phone"><?php echo JText::_('Image') ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Title', 'title', @$lists['order_Dir'], @$lists['order']); ?></th>
				<th width="10%" class="hidden-phone"><?php echo JText::_('Album Name') ?></th>
				<th width="10%" class="hidden-phone"><?php echo JText::_('Cover Image') ?></th>
				<th width="20%" class="hidden-phone center"><?php echo JText::_('Featured Image') ?></th>
				<th width="1%" class="hidden-phone" nowrap="nowrap"
					style="color: #3366CC"><?php echo JHTML::_('grid.sort', 'Published', 'published', @$lists['order_Dir'], @$lists['order']); ?></th>
			</tr>
		</thead>
                
                        <?php
		jimport ( 'joomla.filter.output' );
		$imagepath = JURI::base () . "components/com_simplephotogallery/images";
		$imagelogopath = str_replace ( 'administrator/', '', $imagepath );
		
		$j = 0;
		for($i = 0, $n = count ( $rows ); $i < $n; $i ++) {
			$row = $rows [$i];
			$checked = JHTML::_ ( 'grid.id', $i, $row->id );
			$link = JRoute::_ ( 'index.php?option=com_simplephotogallery&view=images&task=edit&cid[]=' . $row->id );
			$link1 = JRoute::_ ( 'index.php?option=com_simplephotogallery&view=images&cid[]=' . $row->id . '&albumid=' . $row->album_id . '&set=1' );
			$link0 = JRoute::_ ( 'index.php?option=com_simplephotogallery&view=images&cid[]=' . $row->id . '&albumid=' . $row->album_id . '&set=0' );
			?>

                               
                                        <tr>
			<td width="9%" align="center"><?php echo $checked; ?></td>
			<!-- <td align="center" width="9%">
                                                <p class="hasTip content" title="Click and Drag" style="padding:6px;">  <img src="<?php echo $imagepath . '/arrow.png'; ?>" alt="move" width="16" height="16" class="handle" /> </p></td> -->
			<td width="9%" align="center" class=""><a
				href="<?php echo $link; ?><?php echo ($appthaAlbumId = JRequest::getInt("albumid"))? "&albumid=".$appthaAlbumId : ""; ?>">
					<img height="50" width="50"
					src="<?php echo JURI::root()."images/photogallery/featured_image/".$row->image; ?>" />
			</a></td>

			<td width="28%" align="center"><a
				href="<?php echo $link; ?><?php echo ($appthaAlbumId = JRequest::getInt("albumid"))? "&albumid=".$appthaAlbumId : ""; ?>"><?php echo $row->title; ?></a>
			</td>
			<td align="center" width="27%"><?php echo $row->album_name; ?></td>
			<td class="center" width="9%">
                                            <?php if ($row->album_cover) { ?>
                                                <a
				href="<?php echo $link0; ?>"
				title="<?php echo JText::_('Unset default Album Image'); ?>">
                                                   <?php echo JHTML::image(JURI::base ().'components/com_simplephotogallery/images/star-icon.png', JText::_('Set as Album Image'), NULL, true, false); ?></a>
                                                    <?php } else { ?>

                                                <a
				href="<?php echo $link1; ?>"
				title="<?php echo JText::_('Set as default Album Image'); ?>">
                                                	<?php echo JHTML::image(JURI::base ().'components/com_simplephotogallery/images/star-empty-icon.png', JText::_('Set as Album Image'), NULL, true, false); ?></a>
                                                <?php } ?>
                                            </td>
			<!--  <td align="center" id="ordertd_<?php echo $row->id; ?>" width="9%" ><?php echo $row->ordering; ?></td>-->
			<td class="center" width="9%"><?php
// 			$featured = $this->published ( $row->is_featured, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix = 'featured' );
			$featured = $row->is_featured;
			if ($featured == "1") {
				$fea = '<a title="Unfeature item"
										onclick="return listItemTask(\'cb' . $i . '\',\'featuredunpublish\')" href="javascript:void(0);">
<img src="components/com_simplephotogallery/images/tick.png" /></a>';
			} else {
				$fea = '<a title="Feature item"
										onclick="return listItemTask(\'cb' . $i . '\',\'featuredpublish\')" href="javascript:void(0);">
											<img src="components/com_simplephotogallery/images/publish_x.png" /></a>';
			}
			
			echo $fea;
			?>
			</td>
			<td class="center" width="9%"><?php
			$published = $row->published;
			if ($published == "1") {
				$pub = '<a title="Unpublish Item"
										onclick="return listItemTask(\'cb' . $i . '\',\'unpublish\')" href="javascript:void(0);">
<img src="components/com_simplephotogallery/images/tick.png" /></a>';
			} else {
				$pub = '<a title="Publish Item"
										onclick="return listItemTask(\'cb' . $i . '\',\'publish\')" href="javascript:void(0);">
											<img src="components/com_simplephotogallery/images/publish_x.png" /></a>';
			}
			
			echo $pub;
			?></td>
		</tr>
                                    
                                
                            <?php
			
			$j ++;
		}
		
		?>
                        
                
                <tr>
			<td colspan="9">

                                <?php if(count($rows)) echo $pageval['pageNav']->getListFooter(); ?>
                    </td>
		</tr>
	</table>
	<?php } ?>
	<input type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="controller" value="images" /> <input type="hidden"
		name="task" value="" /> <input type="hidden" name="boxchecked"
		value="0" /> <input type="hidden" name="filter_order"
		value="<?php echo @$lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir" value="<?php echo @$lists['order_Dir']; ?>" />

</form>
<?php } ?>

<script type="text/javascript">
    function select_albumname() {
        submitvalues1();
        window.open('index.php?option=com_simplephotogallery&view=images&albumid='+album_name_select,'_self',false);
    }
    function submitvalues1() {
        album_name_select = document.getElementById("albumid").value;
    }
</script>

<script
	src='<?php echo JURI::base() . "components/com_simplephotogallery/js/main.js" ?>'
	type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">

    function removerow(removeid)
    {
        if(document.getElementById('upladtable'))
        {
            var table = document.getElementById('upladtable');
            
            if(table.rows.length-1 != 0) {
                table.deleteRow(removeid);
                uploadimage[removeid]="0";                
            } else {
                var table 		= document.getElementById('upladtable');
                var rowCount 	= table.rows.length;
                var row 		= table.insertRow(rowCount);
                var cell1 		= row.insertCell(0);
                
                row.innerHTML = '<td width="5%" align="right" class="">Image:</td> <td width="35%" > <div id="f0-adminForm" > <input type="file" id="myfile"  name="myfile"  onchange="enableUpload(\'adminForm\');"/><input type="button" id="uploadBtn" name="uploadBtn" value="Upload Image" disabled="disabled" onclick="addQueue(\'adminForm\');" /> <div id="nor"><iframe id="uploadvideo_target0" name="uploadvideo_target0" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe></div></div> <div id="f0-upload-progress" style="display:none"><img id="f0-upload-image" src="components/com_simplephotogallery/images/empty.gif" style="float:left;"  alt="Uploading" /><span id="f0-upload-filename" style="float:left;font-size:12px;font-weight:bold;background:#FFAFAE;padding:5px 10px 5px 10px;"> </span><span id="f0-upload-cancel"style="float:left;"><a style="padding-right:10px;" href="javascript:cancelUpload(\'adminForm\');" name="submitcancel">Cancel</a></span><label id="f0-upload-status" style="float:left;padding-right:40px;padding-left:20px;">Uploading</label><span id="f0-upload-message" style="float:left;font-size:12px;background:#FFAFAE;padding:5px 10px 5px 10px;"><b>Upload Failed:</b> User Cancelled the upload</span></div></td>';
                row.innerHTML += '<td width="5%" align="right" class="">Title:</td><td width="5%"><input  type="text" name="title[]" id="title" value=""></td><td width="10%" align="right" class="">Album Name:</td>';
                row.innerHTML += '<td width="5%" align="right" class="">Description:</td><td width="5%"><input  type="text" name="description[]" id="description" value=""></td><td width="5%" align="right" class="">Description:</td>';
                row.innerHTML += '<td width="5%" align="right" class="">Published:</td><td width="10%"><input type="radio" name="published0[]" checked="checked" value="1" />Yes<input type="radio" name="published0[]" value="0" />No</td><td  align="right" colspan="2" width="5%"><input type="button" id="removebtn" value="Remove" name="removebtn" onclick="removerow(0)" /></td>';
                table.deleteRow(removeid);
                uploadimage[removeid] = "0";
            }
        }
    }
</script>