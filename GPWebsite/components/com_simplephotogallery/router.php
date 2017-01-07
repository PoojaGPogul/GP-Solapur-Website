<?php
/**
 * Router file for Simple Photo Gallery
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

/**
 * Function to assign router values
 *
 * @param   object  &$query  query string
 *
 * @return  simplephotogalleryBuildRoute
 */
function simplephotogalleryBuildRoute(&$query) 
{
    $segments 	= array();
    $db 		= JFactory::getDBO();
    $q	 		= $db->getQuery(true);
	
    if (isset($query['view'])) 
    {
        $segments[] 	= $query['view'];
        unset($query['view']);
    }
    
	if(isset($query['albumid']))
	{
		
		$q->clear('select')
			->select(array('id','alias_name'))
			->from('#__simplephotogallery_album')
			->where($db->quoteName('id') . ' = '. $db->quote($query['albumid']));
		$db->setQuery($q);
				
		$albumTitle 	= $db->loadRow();
		$segments[] 	= $albumTitle[1];
		
		unset( $query['albumid'] );
	}
	
	if(isset($query['photoid']))
	{
		$albumID		= (int)$albumTitle[0];
		
		$q->clear()
			->select('alias_name')
			->from('#__simplephotogallery_images')
			->where($db->quoteName('album_id') . ' = '. $db->quote($albumID))
			->where($db->quoteName('sortorder') . ' = '. $db->quote($query['photoid']));
		$db->setQuery($q);
	
		$imageTitle 	= $db->loadResult();
		$segments[] 	= $imageTitle;
		
		unset( $query['photoid'] );
	}
	
    return $segments;
}

/**
 * Function to assign view for the corresponding router value
 *
 * @param   array  $segments  segments
 *
 * @return  simplephotogalleryParseRoute
 */
function simplephotogalleryParseRoute($segments) 
{
	$vars 	= array();
	$db 	= JFactory::getDBO();
	$q 		= $db->getQuery(true);
	
    // view is always the first element of the array
    $count 	= count($segments);
	
    if ($count) 
    {
        switch ($segments[0]) 
        {
            case 'images':
                $vars['view'] = 'images';

                if (isset($segments[1]))
                {
                    $vars['albumid'] 	= $segments[1];
                    $vars['albumid'] 	= str_replace(":", "-", $vars['albumid']);
                    
                    $q->clear('select')
	                    ->select('id')
	                    ->from('#__simplephotogallery_album')
	                    ->where($db->quoteName('alias_name') . ' = '. $db->quote($vars['albumid']));	                    
					$db->setQuery($q);
					
					$albumID 			= $db->loadResult();                    
                    $vars['albumid'] 	= $albumID;
                }
                
                if (isset($segments[2]))
                {
                    $vars['photoid'] 	= $segments[2];
                    $vars['photoid'] 	= str_replace(":", "-", $vars['photoid']);
                    
                    $q->clear()
	                    ->select('sortorder')
	                    ->from('#__simplephotogallery_images')
	                    ->where($db->quoteName('album_id') . ' = '. $db->quote($vars['albumid']))
	                    ->where($db->quoteName('alias_name') . ' = '. $db->quote($vars['photoid']));
                    $db->setQuery($q);
					
					$imageID 			= $db->loadResult();					
                    $vars['photoid'] 	= $imageID;
                }                   
                break;
        }
    }
    return $vars;
}
?>
