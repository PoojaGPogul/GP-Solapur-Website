<?php
/**
 * Album model file for Simple Photo Gallery
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

// Import joomla controller library
jimport( 'joomla.application.component.model' );
jimport('joomla.html.pagination');

global $mainframe;
$mainframe = JFactory::getApplication();

/**
 * Album Administrator Model
 *
 * @package     Joomla.Simple_Photo_Gallery
 * @subpackage  com_simplephotogallery
 * @since       1.5
 */
class SimplephotogalleryModelalbum extends SimplePhotoGalleryModel
{
	/**
	 * This function is to get album list from album table.
	 *
	 * @return	objectarray 	$album 		albumlist 
	 */	 
	function getAlbum()
    {
		global $option, $mainframe;
        $mainframe = JFactory::getApplication();
        
        $db 	= JFactory::getDBO();
        $query 	= $db->getQuery(true);
        
        $filter_order 		= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'album_name', 'cmd' );
        $filter_order_Dir 	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
        $filter_id 			= $mainframe->getUserStateFromRequest( $option.'filter_id',		'filter_id',		'',			'int' );

    	if ($filter_order != "id" && $filter_order != "album_name" && $filter_order != "published") 
    	{
	    	$filter_order 		= "id";
	    	$filter_order_Dir 	= "asc";
        }
        
        // Search filter
        $search 	= $mainframe->getUserStateFromRequest( $option.'search','search','','string' );
        
        // Page navigation
        $limit 		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');        
        $limitstart = $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');       
       
        $query->clear()
		        ->select('count(*)')
		        ->from('#__simplephotogallery_album');        		        
        $db->setQuery($query);       
        $total 	= $db->loadResult();
        
        $pageNav = new JPagination($total, $limitstart, $limit);

        $lists['order_Dir']	= $filter_order_Dir;
        $lists['order']		= $filter_order;

        if($filter_order) 
        {
            // Sorting order
        	$query->clear('select')
		        	->select('*');
        	
            if(isset($lists['order_Dir']) && isset($lists['order']) && $lists['order'] !="ordering" )
            {               
                $query->order($db->escape($lists['order'] . ' ' . $lists['order_Dir']));
            }            
            
            $db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
            $rows = $db->loadObjectList();
        }
        
        if (trim($search) !="" ) 
        {
            // Sorting order
        	$query->clear('order')
            		->where($db->quoteName('album_name'). ' LIKE '. $db->quote('%' . $search . '%', false));

            if(isset($lists['order_Dir']) && isset($lists['order']) && $lists['order'] !="ordering" )
            {
            	$query->order($db->escape($lists['order'] . ' ' . $lists['order_Dir']));
            }  
                     
            $db->setQuery($query);
            $rows = $db->loadObjectList();
        }
        
        if ($db->getErrorNum()) 
        {
            echo $db->stderr();
            return false;
        }
        
        $album = array('pageNav' => $pageNav,'limitstart'=>$limitstart,'lists'=>$lists,'option'=>$option,'row'=>$rows);
        
        return $album;
    }
    
    /**
     * This function is to save album details into database. 
     *
     * @param 	array 	$album 		album form values
     * 
     * @return  void
     */
	function saveAlbum($album)
	{
		if(!JRequest::getString('alias_name'))
		{
			$aliasName = JRequest::getString('album_name');		
			$aliasName = strtolower(trim($aliasName));
			$aliasName = str_replace(' ','-',$aliasName);
		}
		else 
		{
			$aliasName = JRequest::getString('alias_name');
			$aliasName = strtolower(trim($aliasName));
			$aliasName = str_replace(' ','-',$aliasName);
		}
		
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);

		$query->select('id')
		->from('#__simplephotogallery_album');

		if(JRequest::getInt('id'))
		{
			$albumId = JRequest::getInt('id');
			$query->where($db->quoteName('id') . ' != '. $db->quote($albumId))
					->where($db->quoteName('alias_name') . ' = '. $db->quote($aliasName));			
		}
		else
		{
			$query->clear('where')
					->where($db->quoteName('alias_name') . ' = '. $db->quote($aliasName));
		}

		$db->setQuery( $query);
        $total = $db->loadResult();
		
		if(!empty($total))
        {
        	$aliasName = $aliasName.'-'.time();
        }
        
		$albumTableRow = JTable::getInstance('album', 'Table');

        if (!$albumTableRow->bind($album)) 
        {
            JError::raiseError(500, 'Error binding data');
        }
        
        $albumTableRow->alias_name = $aliasName;
        
        if (!$albumTableRow->check()) 
        {
            JError::raiseError(500, 'Invalid data');
        }
        
        if (!$albumTableRow->store()) 
        {
            $errorMessage = $albumTableRow->getError();
            JError::raiseError(500, 'Error binding data: '.$errorMessage);
        }
	}
	
	/**
	 * This function is to get album details from the table based on album id.
	 *
	 * @param 	int				$id		album id
	 * 
	 * @return 	objectarray		$album 	albumdetails for the given id
	 * 
	 * @return  array
	 */	
	function getAlbums($id)
    {
        global $option;
        
        $row 	= JTable::getInstance('album', 'Table');
        $cid 	= JRequest::getVar( 'cid', array(0), '', 'array' );
        $id 	= $cid[0];
        $key 	= $row->load($id);
        $lists['published'] = JHTML::_('select.booleanlist', 'published','class="inputbox"', $row->published);
        $album 	= array('option'=>$option,'row'=>$row,'lists'=>$lists);
        
        return $album;
    }
    
    /**
     * This function is to publish or unpublish the album from the list.
     *
     * @param	array	$arrayIDs	array of album id
     * 
     * @return  void
     */
	function publish($arrayIDs)
    {        
    	$db 	= $this->getDBO();
    	$query 	= $db->getQuery(true);
    	
    	if($arrayIDs['task']=="publish")
        {
            $publish = 1;
        }
        else
        {
            $publish = 0;
        }
        
        $n 	= count($arrayIDs['cid']);
        
        for($i = 0; $i < $n; $i++)
        {        	
        	$query->clear()
		        	->update($db->quoteName('#__simplephotogallery_album'))
		        	->set($db->quoteName('published') . ' = ' . $db->quote($publish))
		        	->where($db->quoteName('id') . ' = ' . $db->quote($arrayIDs['cid'][$i]));
        	$db->setQuery($query);
        	$db->query();
        }
    }
    
    /**
     * This function is to delete the album from the list.
     *
     * @param	array	$arrayIDs	array of album id
     * 
     * @return  void
     */    
 	function deleteAlbum($arrayIDs)
    {
        $db 	= $this->getDBO();
        $query 	= $db->getQuery(true);

        $query->clear()
        ->delete($db->quoteName('#__simplephotogallery_album'))
        ->where($db->quoteName('id') . ' IN (' . implode(',', $arrayIDs) . ')');
        $db->setQuery($query);
        $db->query();

        $query->clear()
		        ->select('count(*)')
		        ->from('#__simplephotogallery_album');
        $db->setQuery($query);
        $total = $db->loadResult();
    }
}