<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     iCagenda
 * @subpackage  utilities
 * @copyright   Copyright (c)2014-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.4.1 2015-01-23
 * @since       3.4.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * class icagendaCategories
 */
class icagendaEvents
{
	/**
	 * Function to return event access (access levels, approval and event access status)
	 *
	 * @access	public static
	 * @param	$id - id of the event
	 * @return	list array of access levels, approval and event access status
	 *
	 * @since	3.4.0
	 */
	static public function eventAccess($id = null)
	{
		// Preparing connection to db
		$db = Jfactory::getDbo();

		// Preparing the query
		$query = $db->getQuery(true);
		$query->select('e.state AS evtState, e.approval AS evtApproval, e.access AS evtAccess')
			->from($db->qn('#__icagenda_events').' AS e')
			->where($db->qn('e.id').' = '.$db->q($id));
		$query->select('v.title AS accessName')
			->join('LEFT', $db->quoteName('#__viewlevels') . ' AS v ON v.id = e.access');
		$db->setQuery($query);
		$eventAccess = $db->loadObject();

		if ($eventAccess)
		{
			return $eventAccess;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to return feature Icons for an event
	 *
	 * @access	public static
	 * @param	$id - id of the event
	 * @return	list array of feature icons
	 *
	 * @since	3.4.0
	 */
	public static function featureIcons($id = null)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT f.icon, f.icon_alt');
		$query->from('`#__icagenda_feature_xref` AS fx');
		$query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
		$query->where('fx.event_id=' . $id);
		$query->order('f.ordering DESC'); // Order descending because the icons are floated right
		$db->setQuery($query);
		$feature_icons = $db->loadObjectList();

		return $feature_icons;
	}

	/**
	 * Function to return footer list of events
	 *
	 * @since	3.4.0
	 */
	public static function isListOfEvents()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$list_of_events = $params->get('copy', '');
		$core = $params->get('icsys');
		$string = '<a href="ht';
		$string.= 'tp://icag';
		$string.= 'enda.jooml';
		$string.= 'ic.com" target="_blank" style="font-weight: bold; text-decoration: none !important;">';
		$string.= 'iCagenda';
		$string.= '</a>';
		$icagenda = JText::sprintf('ICAGENDA_THANK_YOU_NOT_TO_REMOVE', $string);
		$default = '&#80;&#111;&#119;&#101;&#114;&#101;&#100;&nbsp;&#98;&#121;&nbsp;';
		$footer = '<p><div style="text-align: center; font-size: 10px; text-decoration: none">';
		$footer.= preg_match('/iCagenda/',$icagenda) ? $icagenda : $default . $string;
		$footer.= '</div></p>';

		if ($list_of_events || $core == 'core')
		{
			echo $footer;
		}
	}

	/**
	 * Function to return time formated depending on AM/PM option
	 * Format Time (eg. 00:00 (AM/PM))
	 * $oldtime to be removed (not used since 2.0.0)
	 *
	 * @since 3.4.1
	 */
	public static function dateToTimeFormat($evt, $oldtime = null)
	{
		$app			= JFactory::getApplication();
		$params			= $app->getParams();
		$timeformat		= $params->get('timeformat', 1);
		$eventTimeZone	= null;

		$date_time		= strtotime(JHtml::date($evt, 'Y-m-d H:i', $eventTimeZone));
 		$t_time			= date('H:i', $date_time);

		$time_format	= ($timeformat == 1) ? '%H:%M' : '%I:%M %p';
		$lang_time		= strftime($time_format, strtotime($t_time));

		$time = ($oldtime != NULL) ? $oldtime : JText::_($lang_time);

		return $time;
	}
}
