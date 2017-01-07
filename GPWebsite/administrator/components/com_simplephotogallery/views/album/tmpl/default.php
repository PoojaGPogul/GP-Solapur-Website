<?php
/**
 * Album view file for Simple Photo Gallery
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
<script language="JavaScript" type="text/javascript">
	function submitbutton(pressbutton)
	{
        var form=document.adminForm;

        if(pressbutton=="save" || pressbutton=="apply")
        {
            if (form.album_name.value == '')
            {
                alert("<?php  echo JText::_('Please enter album name') ?>");
                document.getElementById('album_name').focus();
                return false;
            }

            if (form.album_name.value != "")
            {
            	if(!isAliasName(form.album_name.value)){
                	alert("<?php  echo JText::_('Special characters are not allowed in album name') ?>");
                    document.getElementById('album_name').focus();
                    return false;
            	}
            }

            if (form.alias_name.value != "")
            {
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

    Joomla.submitbutton = function(pressbutton)
	{
        var form=document.adminForm;

        if(pressbutton=="save" || pressbutton=="apply")
        {
            if (form.album_name.value == '')
            {
                alert("<?php  echo JText::_('Please enter album name') ?>");
                document.getElementById('album_name').focus();
                return false;
            }

            if (form.album_name.value != "")
            {
            	if(!isAliasName(form.album_name.value)){
                	alert("<?php  echo JText::_('Special characters are not allowed in album name') ?>");
                    document.getElementById('album_name').focus();
                    return false;
            	}
            }

            if (form.alias_name.value != "")
            {
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
if (JRequest::getVar ( 'task' ) == 'edit' || JRequest::getVar ( 'task' ) == '') {
	$rows = $this->album ['row'];
	$lists = $this->album ['lists'];
}

if (JRequest::getVar ( 'task' ) == 'edit' || JRequest::getVar ( 'task' ) == 'add') {
	$taskName = JRequest::getVar ( 'task' );
	?>
<!-- Display this form when task is edit or add -->
<form
	action="<?php echo JRoute::_('index.php?option=com_simplephotogallery&view=album'); ?>"
	method="post" name="adminForm" id="adminForm"
	enctype="multipart/form-data">
	<fieldset class="adminform" style="background-color: white">
		<legend><?php  echo JText::_('Albums') ?></legend>
		<input type="hidden" value="<?php echo $taskName?>" id="hdntask" />
		<table class="admintable" style="width: 65%;" cellspacing="2">
			<tr>
				<td width="150" class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Album Name') ?>::<?php  echo JText::_('Album Name') ?>"><?php  echo JText::_('Album Name') ?><font
						color="red">*</font></span></td>
				<td><input type="text" style="width: 380px" name="album_name"
					id="album_name"
					value="<?php if($taskName=='edit'){echo $rows->album_name;}?>"></td>
			</tr>
			<tr>
				<td width="150" class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Alias Name') ?>::<?php  echo JText::_('Alias Name') ?>"><?php  echo JText::_('Alias Name') ?></span>
				</td>
				<td><input type="text" style="width: 380px" name="alias_name"
					id="alias_name"
					value="<?php if($taskName=='edit'){echo $rows->alias_name;}?>"></td>
			</tr>
			<tr>
				<td width="150" class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('Album Description') ?>::<?php  echo JText::_('Album Description') ?>"><?php  echo JText::_('Album Description') ?></span>
				</td>
				<td><textarea rows="5" cols="5" style="width: 380px;"></textarea></td>
			</tr>
			<tr>
				<td width="100" class="key"><span class="editlinktip hasTip"
					title="<?php  echo JText::_('published') ?>::<?php  echo JText::_('published') ?>"> <?php  echo JText::_('published') ?></span>
				</td>
				<td><input type="radio" style="float: left; margin: 6px 0 0 5px;"
					name="published"
					<?php
	if ($taskName == 'edit') {
		if ($this->album ['row']->published == 1) {
			echo 'checked="checked" ';
		}
	} else {
		echo "checked='checked'";
	}
	?>
					value="1" /> <span style="float: left; margin: 4px 0 0 5px;"><label><?php  echo JText::_('Yes') ?></label></span>
					<input type="radio" style="float: left; margin: 6px 0 0 5px;"
					name="published"
					<?php
	if ($taskName == 'edit') {
		if ($this->album ['row']->published == 0) {
			echo 'checked="checked" ';
		}
	}
	?>
					value="0" /> <span style="float: left; margin: 4px 0 0 5px"><label><?php  echo JText::_('No') ?></label></span>
				</td>
			</tr>
		</table>
	</fieldset>

	<input type="hidden" name="id"
		value="<?php if($taskName=='edit'){echo $rows->id;} ?>" /> <input
		type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="controller" value="album" /> <input type="hidden"
		name="task" value="" /> <input type="hidden" name="boxchecked"
		value="1" />
</form>
<?php
} else {
	$pagesna = $this->album;
	$mainframe = JFactory::getApplication ();
	if (! isset ( $option ))
		$option = '';
	$search = $mainframe->getUserStateFromRequest ( $option . 'search', 'search', '', 'string' );
	?>
<form
	action="<?php echo JRoute::_('index.php?option=com_simplephotogallery&view=album'); ?>"
	method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
                        <?php echo JText::_( 'Search' ); ?> :
                        <input type="text" name="search" id="search"
				value="<?php if (isset($search)) echo $search;?>" class="text_area"
				onchange="document.adminForm.submit();" />
				<button onClick="this.form.submit();"  style="margin-bottom: 9px;"><?php echo JText::_( 'Go' ); ?></button>
				<button onClick="document.getElementById('search').value='';"  style="margin-bottom: 9px;"><?php  echo JText::_( 'Reset' ); ?></button>
			</td>
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
                    <?php if(JRequest::getCmd("tmpl")==""){ ?>
                    <th width="5%" class="hidden-phone"><input
					type="checkbox" name="toggle" value=""
					onclick="
						<?php if (!version_compare(JVERSION, '3.0.0', 'ge')) { ?>
								checkAll(<?php echo count( $rows ); ?>);
					<?php }else{ ?>
								Joomla.checkAll(this);
					<?php } ?>
					" /></th>
					<?php } ?>
                        <th width="10%" class="hidden-phone"><?php echo JHTML::_('grid.sort',  JText::_( 'ID') , 'id', @$lists['order_Dir'], @$lists['order'] ); ?></th>
				<th class="hidden-phone" style="color: #3366CC">
                            <?php
		echo JHTML::_ ( 'grid.sort', 'Album Name', 'album_name', @$lists ['order_Dir'], @$lists ['order'] );
		?>
                        </th>
				<th width="1%" class="hidden-phone" nowrap="nowrap"
					style="color: #3366CC"><?php echo JHTML::_('grid.sort',  JText::_( 'Published' ), 'published', @$lists['order_Dir'], @$lists['order'] ); ?></th>
					<th width="45%"></th>
			</tr>
		</thead>
                <?php
		jimport ( 'joomla.filter.output' );
		$j = 0;
		
		for($i = 0, $n = count ( $rows ); $i < $n; $i ++) {
			$row = $rows [$i];
			$checked = JHTML::_ ( 'grid.id', $i, $row->id );
			$link = JRoute::_ ( 'index.php?option=com_simplephotogallery&view=album&task=edit&cid[]=' . $row->id );
			?>
                        <tr>
                        	<?php if(JRequest::getCmd("tmpl")==""){ ?><td
				align="center"><?php echo $checked; ?></td><?php }?>
                            <td><?php echo $row->id; ?></td>
	                            <?php if(JRequest::getCmd("tmpl")){ ?>
	                            	<td><a class="pointer"
				onclick="if (window.parent) window.parent.jSelectArticle('<?php echo $row->id;?>', '<?php echo $row->album_name;?>');"><?php echo $row->album_name;?></a></td>
	                            <?php } else{ ?>
									<td><a href="<?php echo $link; ?>"><?php echo $row->album_name;?></a></td>
								<?php } ?>
                            <td class="center"><?php
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
			<td></td>
		</tr>
                            <?php
			$j ++;
		}
		?>
                <tr>
			<td colspan="6"><?php if(count($rows))  echo $pagesna['pageNav']->getListFooter(); ?></td>
		</tr>
	</table>
<?php }?>
	<input type="hidden" name="option" value="com_simplephotogallery" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="album" /> <input type="hidden" name="filter_order"
		value="<?php echo $lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />

</form>
<?php }?>