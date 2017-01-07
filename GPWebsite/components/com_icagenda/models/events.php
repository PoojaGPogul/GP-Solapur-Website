<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.4.1 2015-01-14
 * @since       3.4.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * This models supports retrieving lists of events.
 */
class iCagendaModelEvents extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array		An optional associative array of configuration settings.
	 * @see		JController
	 * @since	3.4.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'e.id',
				'ordering', 'e.ordering',
				'state', 'e.state',
				'access', 'e.access', 'access_level',
				'approval', 'e.approval',
				'created', 'e.created',
				'title', 'e.title',
				'username', 'e.username',
				'email', 'e.email',
				'category', 'e.category',
				'cat_color', 'e.catcolor',
				'image', 'e.image',
				'file', 'e.file',
				'next', 'e.next',
				'place', 'e.place',
				'city', 'e.city',
				'country', 'e.country',
				'desc', 'e.desc',
				'language', 'e.language',
				'location', 'e.location',
				'category_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * NOT IN USE CURRENTLY
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the filter state.
		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the filter language.
		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '', 'string');
		$this->setState('filter.language', $language);

		// Filter (dropdown) category
		$category = $this->getUserStateFromRequest($this->context.'.filter.category', 'filter_category');
		$this->setState('filter.category', $category);

		// Filter categoryId
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		// Filter (dropdown) upcoming
		$upcoming = $this->getUserStateFromRequest($this->context.'.filter.upcoming', 'filter_upcoming', '', 'string');
		$this->setState('filter.upcoming', $upcoming);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// List state information.
		parent::populateState('e.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	3.4.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.category');
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.category_id'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	3.4.0
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$user	= JFactory::getUser();

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'e.*'
			)
		);
		$query->from('`#__icagenda_events` AS e');

		// Join over the language
		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = e.language');

		// Join over the users for the checked out user.
//		$query->select('uc.name AS editor');
//		$query->join('LEFT', '#__users AS uc ON uc.id=e.checked_out');

		// Join over the asset groups.
//		$query->select('ag.title AS access_level')
//			->join('LEFT', '#__viewlevels AS ag ON ag.id = e.access');

		// Join the category
		$query->select('c.title AS category, c.color AS catcolor');
		$query->join('LEFT', '#__icagenda_category AS c ON c.id = e.catid');
		$query->where('c.state = 1');

		// Features - extract the number of displayable icons per event
		$query->select('feat.count AS features');
		$sub_query = $db->getQuery(true);
		$sub_query->select('fx.event_id, COUNT(*) AS count');
		$sub_query->from('`#__icagenda_feature_xref` AS fx');
		$sub_query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
		$sub_query->group('fx.event_id');
		$query->leftJoin('(' . (string) $sub_query . ') AS feat ON e.id=feat.event_id');

		// Join Total of registrations
		$query->select('r.count AS registered');
		$sub_query = $db->getQuery(true);
		$sub_query->select('r.eventid, sum(r.people) AS count');
		$sub_query->from('`#__icagenda_registration` AS r');
		$sub_query->where('r.state > 0');
		$sub_query->group('r.eventid');
		$query->leftJoin('(' . (string) $sub_query . ') AS r ON e.id=r.eventid');

		// Join over the users for the author.
//		$query->select('ua.name AS author_name, ua.username AS author_username')
//			->join('LEFT', '#__users AS ua ON ua.id = e.created_by');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('e.state = '.(int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(e.state IN (0, 1))');
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('e.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Filter by search in title
//		$search = $this->getState('filter.search');

//		if (!empty($search))
//		{
//			if (stripos($search, 'id:') === 0)
//			{
//				$query->where('e.id = '.(int) substr($search, 3));
//			}
//			else
//			{
//				$search = $db->Quote('%'.$db->escape($search, true).'%');
//				$query->where('( e.title LIKE '.$search.' OR e.username LIKE '.$search.' OR e.id LIKE '.$search.' OR e.email LIKE '.$search.' OR e.file LIKE '.$search.' OR e.place LIKE '.$search.' OR e.city LIKE '.$search.' OR e.country LIKE '.$search.' OR e.desc LIKE '.$search.' OR c.title LIKE '.$search.')');
//			}
//		}

		// Filter by categories.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId) && !empty($categoryId))
		{
			$query->where('e.catid = ' . $categoryId . '');
		}
		elseif (is_array($categoryId) && !empty($categoryId) && !in_array('0', $categoryId))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('e.catid IN (' . $categoryId . ')');
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('e.access IN (' . $groups . ')');
//				->where('c.access IN (' . $groups . ')'); // To be added later, when access integrated to category
		}

		// Filter Upcoming Dates
		$upcoming = $this->getState('filter.upcoming');

		$config = JFactory::getConfig();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$offset = $config->get('offset');
		}
		else
		{
			$offset = $config->getValue('config.offset');
		}

		// Get a date object based on UTC.
		$joomlaTZ_datetime = JFactory::getDate('now', $offset);
		$joomlaTZ_date = date('Y-m-d', strtotime($joomlaTZ_datetime));

		if (!empty($upcoming))
		{
			if ($upcoming == '1')
			{
				$where_current_upcoming = $db->qn('e.next') . ' >= ' . $db->q($joomlaTZ_date);
				$where_current_upcoming.= ' OR (' . $db->qn('e.next') . ' < ' . $db->q($joomlaTZ_datetime) . ' AND ' . $db->qn('e.startdate') . ' <> "0000-00-00 00:00:00" AND ' . $db->qn('e.enddate') . ' > ' . $db->q($joomlaTZ_datetime) . ')';

				$query->where($where_current_upcoming); // COM_ICAGENDA_OPTION_TODAY_AND_UPCOMING
//				$query->where($db->qn('e.next').' >= '.$db->q($joomlaTZ_datetime)); // COM_ICAGENDA_OPTION_TODAY_AND_UPCOMING
			}
			elseif ($upcoming == '2')
			{
				$where_past = '(';

				// Period dates with no weekdays filter
				$where_past.= $db->qn('e.next') . ' < ' . $db->q($joomlaTZ_datetime) . ')';
				$where_past.= ' AND (' . $db->qn('e.enddate') . ' < ' . $db->q($joomlaTZ_datetime);

				$where_past.= ' )';

				$query->where($where_past); // COM_ICAGENDA_OPTION_PAST
//				$query->where($db->qn('e.next').' < '.$db->q($joomlaTZ_date)); // COM_ICAGENDA_OPTION_PAST
			}
			elseif ($upcoming == '3')
			{
				$where_upcoming = '(';
				$where_upcoming.= $db->qn('e.next') . ' > ' . $db->q($joomlaTZ_datetime);
//				$where_upcoming.= ' OR (' . $db->qn('e.next') . ' < ' . $db->q($joomlaTZ_datetime) . ' AND ' . $db->qn('e.startdate') . ' <> "0000-00-00 00:00:00" AND ' . $db->qn('e.startdate') . ' > ' . $db->q($joomlaTZ_datetime) . ')';
				$where_upcoming.= ' )';

				$query->where($where_upcoming); // COM_ICAGENDA_OPTION_FUTURE
//				$query->where($db->qn('e.next').' >= '.$db->q($joomlaTZ_date) . ' + INTERVAL 1 DAY'); // COM_ICAGENDA_OPTION_FUTURE
			}
			elseif ($upcoming == '4') // COM_ICAGENDA_OPTION_TODAY
			{
				$where_today = '( ';

				// One day dates filter
				$where_today.= ' (';
				$where_today.= ' (' . $db->qn('e.next') . ' >= ' . $db->q($joomlaTZ_datetime) . ')';
				$where_today.= ' AND (' . $db->qn('e.next') . ' < ' . $db->q($joomlaTZ_date) . ' + INTERVAL 1 DAY)';
				$where_today.= ' )';

				// Period dates with no weekdays filter
				$where_today.= ' OR ( ';
//				$where_today.= ' (' . $db->qn('e.next') . ' >= ' . $db->q($joomlaTZ_datetime) . ')';
				$where_today.= ' (' . $db->qn('e.next') . ' > ' . $db->q($joomlaTZ_date) . ')';
				$where_today.= ' AND (' . $db->qn('e.weekdays') . ' = "")';
				$where_today.= ' AND ' . $db->qn('e.enddate') . ' <> "0000-00-00 00:00:00" AND (' . $db->qn('e.enddate') . ' >= ' . $db->q($joomlaTZ_date) . ')';
				$where_today.= ' AND ' . $db->qn('e.startdate') . ' <> "0000-00-00 00:00:00" AND (' . $db->qn('e.startdate') . ' < ' . $db->q($joomlaTZ_date) . ')';
				$where_today.= ' )';

				$where_today.= ' )';

				$query->where($where_today); // COM_ICAGENDA_OPTION_TODAY
//				$query->where($db->qn('e.next').' >= '.$db->q($joomlaTZ_date));
//				$query->where($db->qn('e.next').' < ('.$db->q($joomlaTZ_date).' + INTERVAL 1 DAY)');
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol.' '.$orderDirn));
		}

		return $query;
	}
}
