<?php
/**
 * Images model file for Simple Photo Gallery
 *
 * @category   Apptha
 * @package    Com_Simplephotogallery
 * @version    1.1
 * @author     Apptha Team <developers@contus.in>
 * @copyright  Copyright (C) 2014 Apptha. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import joomla model library
jimport( 'joomla.application.component.model' );

// Import joomla pagination library
jimport( 'joomla.html.pagination' );

global $mainframe;
$mainframe = JFactory::getApplication();

/**
 * Images Administrator Model
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryModelimages extends SimplePhotoGalleryModel
{
	/**
	 * This function is to get images list from images table.
	 *
	 * @return	array
	 */
    function getimage() {

        global $option, $mainframe, $albumtot, $albumval;

        // Table ordering
        $mainframe 			= JFactory::getApplication();
        $db 				=  JFactory::getDBO();
        $query 				= $db->getQuery(true);
        $filter_order 		= $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
        $filter_order_Dir 	= $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'word');
        $filter_id 			= $mainframe->getUserStateFromRequest($option . 'filter_id', 'filter_id', '', 'int');
        $albumid 			= "";

        // Search filter
        $search 			= $mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string');

        // Page navigation
        $limit 				= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart 		= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] 	= $filter_order;

        $query->select('*')
        	->from('#__simplephotogallery_album')
        	->order($db->escape('id' . ' ' . 'ASC'));
        $db->setQuery($query);
        $albumval 			= $db->loadObjectList();
        $albumid 			= JRequest::getVar('albumid');

        if ($albumid)
        {
            $albumid = $albumid;
        }
        else
        {
        	$albumid = isset($albumval[0]->id)?$albumval[0]->id:'';
        }

        $query->clear()
        ->select('count(*)')
        ->from('#__simplephotogallery_images')
        ->where($db->quoteName('album_id') . ' = ' . $db->quote($albumid));
        $db->setQuery($query);
        $total = $db->loadResult();
        
        $pageNav = new JPagination($total, $limitstart, $limit);

        if ($filter_order) 
        {
            $query->clear()
            	->select(array('a.*', 'b.album_name'))
				->from('#__simplephotogallery_images AS a')
				->leftJoin('#__simplephotogallery_album AS b ON b.id = a.album_id');

	            if ((JRequest::getVar('album_id', '', 'get', 'int')) != "")
	            {
	            	$albumid = JRequest::getVar('albumid', '', 'get', 'int');
	            	$query->where($db->quoteName('album_id') . ' = ' . $db->quote($albumid));
	            	JRequest::setVar('hid_albumid',$albumid);
	            }
	            else if ($albumid != "")
	            {
	            	$query->where($db->quoteName('album_id') . ' = ' . $db->quote($albumid));
	            }

           	$query->order($filter_order . ' ' . $filter_order_Dir);
            $db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
            $rows = $db->loadObjectList();
        }

        if ($search) 
        {
            $query->clear()
            	->select(array('a.*', 'b.album_name'))
				->from('#__simplephotogallery_images AS a')
				->leftJoin('#__simplephotogallery_album AS b ON b.id = a.album_id')
            	->where($db->quoteName('title') . ' LIKE ' . $db->quote('%' . $search . '%'));
            $db->setQuery($query);
            $rows = $db->loadObjectList();
        }

        if ($db->getErrorNum()) 
        {
            echo $db->stderr();
            return false;
        }

        $images = array('pageNav' => $pageNav, 'limitstart' => $limitstart, 'lists' => $lists, 'option' => $option, 'row' => $rows, 'albumval' => $albumval, 'album' => $albumid);

        return $images;
    }

    /**
     * This function is to get setting from the database
     *
     * @return	objectarray
     */
	function settings()
    {
        global $option, $mainframe;
        $db 	= JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('count(*)')
        	->from('#__simplephotogallery_settings');
        $db->setQuery( $query);
        $total = $db->loadResult();

        $query->clear()
        	->select('*')
        	->from('#__simplephotogallery_settings');
        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
        $settings = array('option'=>$option,'row'=>$rows);
        return $settings;
    }

    /**
     * This function is to get album list
     *
     * @return	array
     */
	function getNewimages() 
	{
        $imagesTableRow 				= JTable::getInstance('images', 'Table');
        $imagesTableRow->id				= 0;
        $imagesTableRow->image 			= '';
        $imagesTableRow->image 			= '';
        $imagesTableRow->title 			= '';
        $imagesTableRow->description 	= '';
        $imagesTableRow->ordering 		= '';
        $imagesTableRow->singleimg 		= '';
        $imagesTableRow->published 		= '';
        $imagesTableRow->albumid 		= '';
        $imagesTableRow->albcover 		= '';

        $db = JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('*')
        ->from('#__simplephotogallery_album');
        $db->setQuery($query);
        $albumval = $db->loadObjectList();

        $images = array('imagesTableRow' => $imagesTableRow, 'albumval' => $albumval);

        return $images;
    }

    /**
     * Function to save new images
     *
     * @param   object  $images  image details
     *
     * @return  void
     */
	function saveImagesNew($images)
	{
		$db 		= JFactory::getDBO();
		$query		= $db->getQuery(true);
		$alias_name = $images['alias_name'];

		if (trim($alias_name) == '')
		{
			$alias_name = $alias_name;
		}

		$alias_name = JApplication::stringURLSafe($alias_name);

		if (trim(str_replace('-', '', $alias_name)) == '')
		{
			$alias_name = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		$table = $this->getTable('images');

		while ($table->load(array('alias_name' => $alias_name)) && empty($images[id]))
		{
			$alias_name = JString::increment($alias_name, 'dash');
		}

		$albumId 	= $images['album_id'];

		$query->select('sortorder')
			->from('#__simplephotogallery_images')
			->where($db->quoteName('album_id') . ' = ' . $db->quote($albumId))
			->order($db->escape('id' . ' ' . 'DESC'));
    	$db->setQuery( $query, 0, 1 );
       	$rows = $db->loadRow();

		if(!empty($images['id']))
		{
			$query->clear()
				->select('id')
				->from('#__simplephotogallery_images')
				->where($db->quoteName('id') . ' != ' . $db->quote($images['id']))
				->where($db->quoteName('album_id') . ' != ' . $db->quote($albumId))
				->where($db->quoteName('alias_name') . ' != ' . $db->quote($alias_name));
		}
		else 
		{
			$query->clear()
			->select('id')
			->from('#__simplephotogallery_images')
			->where($db->quoteName('album_id') . ' != ' . $db->quote($albumId))
			->where($db->quoteName('alias_name') . ' != ' . $db->quote($alias_name));
		}

		$db->setQuery( $query);
        $total = $db->loadResult();
	    $imagesTableRow = $this->getTable('images');

	    if (!$imagesTableRow->bind($images)) 
	    {
	    	JError::raiseError(500, 'Error binding data');
	    }

       	$imagesTableRow->alias_name = $alias_name;
        
		if(!isset($rows[0]))
		{
			$imagesTableRow->sortorder = 1;
		}
		else 
		{
	    	$imagesTableRow->sortorder = $rows[0] + 1;
		}

	    if (!$imagesTableRow->check()) 
	    {
	    	JError::raiseError(500, 'Invalid data');
	    }

		if (!$imagesTableRow->store()) 
		{
	    	$this->setError($this->_db->getErrorMsg());
	        return false;
	    }
	}

	/**
	 * This function is to set cove image for album
	 *
	 * @return	void
	 */
	function setImage() 
	{
        $row 	= JTable::getInstance('images', 'Table');
        $cid 	= JRequest::getVar('cid', array(0), 'get', 'array');
        $aid 	= JRequest::getVar('albumid', array(0), 'get', 'int');
        $set 	= JRequest::getVar('set', array(0), 'get', 'int');
        $db	 	= JFactory::getDBO();
        $query	= $db->getQuery(true);

        if ($set == 1) 
        {
        	$fields = array(
        			$db->quoteName('album_cover') . ' = 0'
        	);
        	 
        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)        		
        		->where($db->quoteName('album_id') . ' = ' . $db->quote($aid));

        	$db->setQuery($query);
        	$db->query();
        	
        	$fields = array(
        			$db->quoteName('album_cover') . ' = 1'
        	);
        	 
        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('id') . ' = ' . $db->quote($cid[0]))
        		->where($db->quoteName('album_id') . ' = ' . $db->quote($aid));

        	$db->setQuery($query);
            $db->query();
        }
        else if ($set == 0) 
        {
        	$fields = array(
        			$db->quoteName('album_cover') . ' = 0'
        	);
        	
        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('id') . ' = ' . $db->quote($cid[0]))
        		->where($db->quoteName('album_id') . ' = ' . $db->quote($aid));

            $db->setQuery($query);
            $db->query();
        }
    }

    /**
     * Function to get album details for edit mode
     *
     * @param   int  $id  image id
     *
     * @return  array
     */
    function getimages($id) {
        global $option, $albumval, $albumtot;

        $row	= JTable::getInstance('images', 'Table');
        $cid	= JRequest::getVar('cid', array(0), '', 'array');
        $id		= $cid[0];
        $row->load($id);

        $db		= JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('*')
        	->from('#__simplephotogallery_album');
        $db->setQuery($query);
        $albumval = $db->loadObjectList();

        $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published);
        $images = array('option' => $option, 'row' => $row, 'lists' => $lists, 'albumval' => $albumval);
        return $images;
    }

    /**
     * Function to update image
     *
     * @param   int  $images  image id
     *
     * @return  void
     */
	function saveimages($images) 
	{
        $db 			= JFactory::getDBO();
        $query			= $db->getQuery(true);
        $title 			= $images['title'];
        $albumid 		= $images['album_id'];
        $description 	= $images['description'];
        $published 		= $images['published'];
        $alias_name 	= $images['alias_name'];
        $albumId 		= JRequest::getVar('album_id');
        $imgfilename = explode("/",JRequest::getVar("image"));

        if (trim($alias_name) == '')
        {
        	$alias_name = $alias_name;
        }
        
        $alias_name = JApplication::stringURLSafe($alias_name);
        
        if (trim(str_replace('-', '', $alias_name)) == '')
        {
        	$alias_name = JFactory::getDate()->format('Y-m-d-H-i-s');
        }
        
        $table = $this->getTable('images');
        
        while ($table->load(array('alias_name' => $alias_name)) && empty($images[id]))
        {
        	$alias_name = JString::increment($alias_name, 'dash');
        }

        if(!isset($imgfilename[2]))
        {
        	$imgfilename[2] = "";
        }

        $fields = array(
        		$db->quoteName('image') . ' = ' . $db->quote($imgfilename[2]),
				$db->quoteName('title') . '= ' . $db->quote($title),
        		$db->quoteName('description') . '= ' . $db->quote($description),
        		$db->quoteName('album_id') . '= ' . $db->quote($albumid),
        		$db->quoteName('alias_name') . '= ' . $db->quote($alias_name),
        		$db->quoteName('published') . '= ' . $db->quote($published)
        );
         
        $query->clear()
        	->update($db->quoteName('#__simplephotogallery_images'))
        	->set($fields)
        	->where($db->quoteName('id') . ' = ' . $db->quote($images[id]));
        $db->setQuery($query);
        $db->query();
        
    }

    /**
     * Function to publish or unpublish the images from the list
     *
     * @param   array  $arrayIDs  image detail array
     *
     * @return  void
     */
	function pubimages($arrayIDs)
	{
		$albumid	= JRequest::getVar('albumid');
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

        if ($arrayIDs['task'] == "publish") 
        {
            $publish = 1;
        } 
        else 
        {
            $publish = 0;
        }

        $n = count($arrayIDs['cid']);

        for ($i = 0; $i < $n; $i++) 
        {
        	$fields = array(
        			$db->quoteName('published') . ' = ' . $db->quote($publish)
        	);
        	 
        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('id') . ' = ' . $db->quote($arrayIDs['cid'][$i]));
            $db->setQuery($query);
            $db->query();    
        }
        
        if($arrayIDs['task'] == "unpublish")
        {
	        for ($i = 0; $i < $n; $i++) 
	        {
	        	$fields = array(
	        			$db->quoteName('sortorder') . ' = sortorder - 1'
	        	);
	        	
	        	$query->clear()
	        		->update($db->quoteName('#__simplephotogallery_images'))
	        		->set($fields)
	        		->where($db->quoteName('album_id') . ' = ' . $db->quote($albumid))
	        		->where($db->quoteName('id') . ' >= ' . $db->quote($arrayIDs['cid'][$i]));
	            $db->setQuery($query);
	            $db->query();    
	        }
        }
        else
        {
        for ($i = 0; $i < $n; $i++) 
        {
        	$fields = array(
        			$db->quoteName('sortorder') . ' = sortorder + 1'
        	);

        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('album_id') . ' = ' . $db->quote($albumid))
        		->where($db->quoteName('id') . ' >= ' . $db->quote($arrayIDs['cid'][$i]));
            $db->setQuery($query);
            $db->query();    
        }
        }

	}

	/**
	 * Function to set featured or unfeatured image
	 *
	 * @param   array  $arrayIDs  image detail array
	 *
	 * @return  void
	 */
	function featuredpublish($arrayIDs)
	{
		$db 			= JFactory::getDBO();
		$query			= $db->getQuery(true);

		if ($arrayIDs['task'] == "featuredpublish") {
            $featured = 1;
        } else {
            $featured = 0;
        }

        $n = count($arrayIDs['cid']);

        for ($i = 0; $i < $n; $i++) 
        {
        	$fields = array(
        			$db->quoteName('is_featured') . ' = ' . $db->quote($featured)
        	);
        	
        	$query->clear()
        		->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('id') . ' = ' . $db->quote($arrayIDs['cid'][$i]));        	
            $db->setQuery($query);
            $db->query();     
        }
	}

	/**
	 * Function to get settings
	 *
	 * @return  array
	 */
	function getsettings()
    {
        global $option, $mainframe;
        $db 	= JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('*')
        	->from('#__simplephotogallery_settings');
        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) 
        {
            echo $db->stderr();
            return false;
        }

        $settings = array('option'=>$option,'row'=>$rows);

        return $settings;
    }

    /**
	 * Function to delete the images from the list
	 *
	 * @param   array  $arrayIDs  image detail array
	 *
	 * @return  void
	 */
 	function deleteimages($arrayIDs) 
 	{
 		$albumid 	= JRequest::getVar('albumid');
 		$n 			= count($arrayIDs);
 		$db 		= JFactory::getDBO();
 		$query		= $db->getQuery(true);

 		for ($i = 0; $i < $n; $i++) 
        {
        	$fields = array(
        			$db->quoteName('sortorder') . ' = sortorder - 1'
        	);
        	 
        	$query->update($db->quoteName('#__simplephotogallery_images'))
        		->set($fields)
        		->where($db->quoteName('album_id') . ' >= ' . $db->quote($arrayIDs[$i]));
            $db->setQuery($query);
            $db->query();     
        }

        $query->clear()
        	->delete($db->quoteName('#__simplephotogallery_images'))
        	->where($db->quoteName('id') . ' IN (' . implode(',', $arrayIDs) . ')');
        $db->setQuery($query);
        $db->query();
    }
}
