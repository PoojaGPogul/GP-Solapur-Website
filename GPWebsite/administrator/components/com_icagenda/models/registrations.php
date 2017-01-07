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
 * @version     3.4.0 2014-06-22
 * @since		2.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of iCagenda records.
 */
class iCagendaModelregistrations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JController
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'userid', 'userid',
				'name', 'name',
				'username', 'username',
				'email', 'email',
				'phone', 'phone',
				'event', 'event',
				'date', 'a.date',
				'people', 'a.people',
				'notes', 'a.notes',
				'created_by', 'created_by'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter (dropdown) state.
		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filter (dropdown) events
		$events = $this->getUserStateFromRequest($this->context.'.filter.events', 'filter_events', '', 'string');
		$this->setState('filter.events', $events);

		// Filter (dropdown) dates
		$dates = $this->getUserStateFromRequest($this->context.'.filter.dates', 'filter_dates', '', 'string');
		$this->setState('filter.dates', $dates);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_icagenda');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
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
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__icagenda_registration` AS a');

		// Join over the users for the checked out user.
		$query->select('u.username AS username');
		$query->join('LEFT', '#__users AS u ON u.id=a.userid');

		// Join over the events for the checked out user.
		$query->select('e.title AS event, e.created_by AS created_by, e.state AS evt_state');
		$query->join('LEFT', '#__icagenda_events AS e ON e.id=a.eventid');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('a.state = '.(int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in content
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				if(version_compare(JVERSION, '3.0', 'lt'))
				{
					$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				}
				else
				{
					$search = $db->Quote('%'.$db->escape($search, true).'%');
				}
				$query->where('(u.username LIKE '.$search.'  OR  a.name LIKE '.$search.'  OR  a.userid LIKE '.$search.'  OR  a.email LIKE '.$search.'  OR  a.phone LIKE '.$search.'  OR  a.date LIKE '.$search.'  OR  a.period LIKE '.$search.'  OR  a.people LIKE '.$search.'  OR  a.notes LIKE '.$search.'  OR  e.title LIKE '.$search.' )');
			}
		}

		// Filter events
		$event = $db->escape($this->getState('filter.events'));
		if (!empty($event))
		{
			$query->where('(a.eventid='.$event.')');
		}

		// Filter dates
		$date = $db->escape($this->getState('filter.dates'));
		if (!empty($date))
		{
			$query->where('(a.date='.$date.')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol && $orderDirn)
		{
			if(version_compare(JVERSION, '3.0', 'lt'))
			{
				$query->order($db->getEscaped($orderCol.' '.$orderDirn));
			}
			else
			{
				$query->order($db->escape($orderCol.' '.$orderDirn));
			}
		}

		return $query;
	}

	/**
	 * Gets a list of all events.
	 */
	function getEvents()
	{
		// Create a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('e.id AS event, e.title AS title');
		$query->from('`#__icagenda_events` AS e');

		// Join over the users for the checked out user.
		$query->select('r.eventid AS eventid');
		$query->join('LEFT', '#__icagenda_registration AS r ON r.eventid=e.id');
		$query->where('(e.id = r.eventid)');

		// Filter by published state
//		$query->where('(e.state IN (0, 1))');

		$db->setQuery($query);
		$events = $db->loadObjectList();

		$list = array();
		foreach ($events as $e)
		{
			$list[$e->event] = $e->title;
		}

		return $list;
	}

	/**
	 * Gets a list of dates.
	 */
	function getDates()
	{
		// Create a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('r.date AS date');
		$query->from('`#__icagenda_registration` AS r');

		$db->setQuery($query);
		$dates = $db->loadObjectList();

		$list = array();
		foreach ($dates as $d)
		{
			$list[$d->date] = $d->date;
		}

		return $list;
	}
}
