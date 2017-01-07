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
 * @version     3.4.1 2015-01-30
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modelitem');
jimport('joomla.html.parameter');
jimport('joomla.registry.registry');

jimport('joomla.user.helper');
jimport('joomla.access.access');

class iCModelItem extends JModelItem
{
	/**
	 * @var
	 */
	protected $msg;
	protected $filters;
	protected $options;
	protected $itObj;
	protected $where;


	/**
	 * Load the iChelper class
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the helper class
		JLoader::register('iCModeliChelper', JPATH_SITE . '/components/com_icagenda/helpers/ichelper.php');
	}


	/**
	 * Model Builder
	 */
	protected function startiCModel()
	{
		$this->filters	= array();
		$this->options	= array();
		$this->items	= array();
		$this->itObj	= new stdClass;
	}


	/**
	 * Table importation
	 */
	public function getTable($type = 'icagenda', $prefix = 'icagendaTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Get all data
	 */
	protected function getItems ($structure)
	{
		// Return Items
		if (isset($this->items) && is_array($this->items))
		{
			$this->items = $this->getDBitems();
		}

		foreach ($structure as $k=>$v)
		{
			$this->itObj->$k = $this->$k($v);
		}

		return $this->itObj;
	}


	/**
	 * Add the filters to be used in queries
	 */
	protected function addFilter($name, $value)
	{
		$this->filters[$name] = $value;
	}


	/**
	 * Add the options you use to obtain the various data in the right setting
	 */
	protected function addOption($name, $value)
	{
		$this->options[$name]=$value;
	}


	/**
	 *
	 * ALL DATES - View with filtering
	 *
	 */

	public static function getAllDates()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get Settings
		$selection_of_events	= $params->get('time', 1);
		$filterTime				= $params->get('time', 1);
		$datesDisplay			= $params->get('datesDisplay', 1);
		$orderby				= $params->get('orderby', 2);

		// Set vars
		$nodate 		= '0000-00-00 00:00:00';
		$ic_nodate		= '0000-00-00 00:00';
		$eventTimeZone	= null;
		$datetime_today	= JHtml::date('now', 'Y-m-d H:i'); // Joomla Time Zone
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

		// Get Data
		$db		= Jfactory::getDbo();
		$query	= $db->getQuery(true);
        $query->select('e.next, e.dates, e.startdate, e.enddate, e.period, e.weekdays, e.id');
        $query->from('#__icagenda_events AS e');
		$query->leftJoin('`#__icagenda_category` AS c ON c.id = e.catid');
		$query->where('c.state = 1');

		// Adding Filter if State Published
		$where= "(e.state = 1)";

		// Adding Filter per Category in Navigation
		$mcatid = $params->get('mcatid');

		if (is_array($mcatid))
		{
			$selcat = implode(', ', $mcatid);

			if (!in_array('0', $mcatid))
			{
				$where.= " AND (e.catid IN ($selcat))";
			}
		}

		// Adding Filter per Feature in Navigation
		$query->where(iCModeliChelper::getFeaturesFilter());

		// Language Control
		$lang = JFactory::getLanguage();
		$langcur = $lang->getTag();
		$langcurrent = $langcur;
		$where.= " AND ((e.language = '$langcurrent') OR (e.language = '*') OR (e.language = NULL) OR (e.language = ''))" ;

		// Access Control
		$user = JFactory::getUser();
		$userID = $user->id;
		$userLevels = $user->getAuthorisedViewLevels();

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$userGroups = $user->getAuthorisedGroups();
		}
		else
		{
			$userGroups = $user->groups;
		}

		$groupid = JComponentHelper::getParams('com_icagenda')->get('approvalGroups', array("8"));

		$groupid = is_array($groupid) ? $groupid : array($groupid);

		// Test if user has Access Permissions
		if (!in_array('8', $userGroups) )
		{
			$useraccess	= implode(', ', $userLevels);
			$where.= ' AND e.access IN ('.$useraccess.')';
		}

		// Test if user logged-in has Approval Rights
		if (!array_intersect($userGroups, $groupid)
			&& !in_array('8', $userGroups))
		{
			$where.= ' AND e.approval <> 1';
		}
		else
		{
			$where.= ' AND e.approval < 2';
		}

		$query->where($where);

		$db->setQuery($query);
		$list = $db->loadObjectList();

		$list_all_dates = array();

		foreach ($list AS $i)
		{
			$i_id			= $i->id;
//			$i_state		= $i->state;
//			$i_next			= $i->next;
			$i_startdate	= $i->startdate;
			$i_enddate		= $i->enddate;
			$i_weekdays		= $i->weekdays;
			$i_dates		= $i->dates;

			// Declare AllDates array
			$AllDatesDisplay	= array();

			// Get WeekDays Array
			$WeeksDays			= iCDatePeriod::weekdaysToArray($i_weekdays);

			// If Single Dates, added each one to All Dates for this event
			$singledates		= unserialize($i_dates);
			$singleDatesArray	= array();

			foreach ($singledates as $sd)
			{
				$date_Dat	= JHtml::date($sd, 'Y-m-d', $eventTimeZone);
				$SingleDate	= JHtml::date($sd, 'Y-m-d H:i', $eventTimeZone);

				$isValid = iCDate::isDate($sd);

				if ($isValid)
				{
					if ($datesDisplay == 1) // Upcoming Events
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
					elseif ($filterTime == 3
						&& strtotime($SingleDate) > strtotime($datetime_today)
						) // Upcoming Events
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
					elseif ($filterTime == 1
						&& strtotime($SingleDate) > strtotime($datetime_today)
						) // Current and Upcoming Events
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
					elseif ($filterTime == 2
						&& strtotime($SingleDate) < strtotime($datetime_today)
						) // Past event (Finished)
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
					elseif ($filterTime == 4
						&& strtotime($SingleDate) >= strtotime($date_today)
						) // Current Today
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
					elseif ($filterTime == ''
						&& strtotime($SingleDate) >= strtotime($datetime_today)
						) // All Dates
					{
						array_push($singleDatesArray, $SingleDate . '_' . $i_id);
					}
				}
			}

			if ($datesDisplay == 2
				&& $filterTime == 2
				&& count($singleDatesArray) > 0) // Past Events
			{
				array_push($AllDatesDisplay, max($singleDatesArray));
			}
			elseif ($datesDisplay == 2
				&& count($singleDatesArray) > 0)
			{
				array_push($AllDatesDisplay, min($singleDatesArray));
			}
			else
			{
				$AllDatesDisplay = array_merge($AllDatesDisplay, $singleDatesArray);
			}

			// If Period Dates, added each one to All Dates for this event (filter week Days, and if date not null)
			$StDate = JHtml::date($i_startdate, 'Y-m-d H:i', $eventTimeZone);
			$EnDate = JHtml::date($i_enddate, 'Y-m-d H:i', $eventTimeZone);

			$date_startdate	= JHtml::date($i_startdate, 'Y-m-d', $eventTimeZone);
			$date_enddate	= JHtml::date($i_enddate, 'Y-m-d', $eventTimeZone);
			$time_startdate	= JHtml::date($i_startdate, 'H:i', $eventTimeZone);
			$time_enddate	= JHtml::date($i_enddate, 'H:i', $eventTimeZone);

			$perioddates = iCDatePeriod::listDates($StDate, $EnDate, $eventTimeZone);

			$period_array = array();

			foreach ($perioddates AS $date_in_weekdays)
			{
				$datetime_period_date = JHtml::date($date_in_weekdays, 'Y-m-d H:i', $eventTimeZone);

				if (in_array(date('w', strtotime($datetime_period_date)), $WeeksDays))
				{
					array_push($period_array, $datetime_period_date);
				}
			}


			$only_startdate = ($i_weekdays || $i_weekdays == '0') ? false : true;

			if (isset($period_array)
				&& ($period_array != NULL)
				)
			{
				if ($only_startdate)
				{
					array_push($AllDatesDisplay, $StDate . '_' . $i_id);
				}
				else
				{
					$dp = 0;
					$count_period = count($period_array);
					$cp = 0;

					foreach ($period_array as $Dat)
					{
						$date_Dat	= JHtml::date($Dat, 'Y-m-d', $eventTimeZone);
						$SingleDate	= JHtml::date($Dat, 'Y-m-d H:i', $eventTimeZone);

						if (in_array(date('w', strtotime($Dat)), $WeeksDays)
							&& $dp == 0
							)
						{
							if ( $date_Dat == $date_today && $filterTime != 3 ) // Not Upcoming Events
							{
								$dp = ($datesDisplay == 2) ? $dp+1 : 0;
								array_push($AllDatesDisplay, $date_Dat . ' ' .$time_enddate . '_' . $i_id);
							}
							elseif ( $SingleDate > $date_today && $filterTime == 3 ) // Upcoming Events
							{
								$dp = ($datesDisplay == 2) ? $dp+1 : 0;
								array_push($AllDatesDisplay, $SingleDate . '_' . $i_id);
							}
							elseif ( $date_Dat >= $date_today ) // Not past event (ongoing or upcoming)
							{
								$dp = ($datesDisplay == 2) ? $dp+1 : 0;
								array_push($AllDatesDisplay, $SingleDate . '_' . $i_id);
							}
							elseif ( $date_Dat < $date_today ) // Past event (Finished)
							{
								$cp = ($datesDisplay == 2) ? $cp+1 : $count_period;

								if ($cp == $count_period)
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;
									array_push($AllDatesDisplay, $SingleDate . '_' . $i_id);
								}
							}
						}
					}
				}
			}

			if ( $datesDisplay == 2
				&& count($AllDatesDisplay) > 0 )
			{
				$ex_min 	= explode('_', min($AllDatesDisplay));
				$ex_date	= $ex_min[0];

				if ($filterTime != '4')
				{
					// min date is upcoming
					if ( $ex_date >= $datetime_today )
					{
						$AllDatesDisplay = array(min($AllDatesDisplay));
					}
					// All events
					elseif ($filterTime == '0')
					{
						// min date in Period
						if (in_array($ex_date, $period_array))
						{
							$AllDatesDisplay = array(min($AllDatesDisplay));
						}
						// min date is Single date and not past
						elseif ( !in_array($ex_date, $period_array)
							&& ($ex_date > $datetime_today) )
						{
							$AllDatesDisplay = array(min($AllDatesDisplay));
						}
						// min date is Single date and past
						else
						{
							$AllDatesDisplay = array(max($AllDatesDisplay));
						}
					}
					else
					{
						$AllDatesDisplay = array(max($AllDatesDisplay));
					}
				}
				else
				{
					$AllDatesDisplay = array(min($AllDatesDisplay));
				}
			}

			$AllDatesFilterTime = array();

			foreach ($AllDatesDisplay as $fD)
			{
				$ex_date		= explode('_', $fD);
				$get_date		= $ex_date['0'];
				$date_get_date	= JHtml::date($get_date, 'Y-m-d', $eventTimeZone);
				$time_enddate	= JHtml::date($EnDate, 'H:i', $eventTimeZone);

				// Filter Dates : All Dates
				if ($filterTime == 0)
				{
					if ( in_array($get_date, $perioddates)
						&& $only_startdate
						&& !in_array($StDate . '_' . $i_id, $AllDatesFilterTime)
						 )
					{
						// Period with no weekdays selected
						array_push($AllDatesFilterTime, $StDate . '_' . $i_id);
					}
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						 )
					{
						// Period with weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif ( !in_array($get_date, $perioddates) )
					{
						// Single Dates
						array_push($AllDatesFilterTime, $fD);
					}
				}
				// Filter Dates : Ongoing and Upcoming
				elseif ($filterTime == 1)
				{
					if ( in_array($get_date, $perioddates)
						&& $only_startdate
						&& strtotime($EnDate) >= strtotime($datetime_today)
						&& !in_array($StDate . '_' . $i_id, $AllDatesFilterTime)
						 )
					{
						// Period with no weekdays selected
						array_push($AllDatesFilterTime, $StDate . '_' . $i_id);
					}
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						&&  strtotime($get_date) >= strtotime($datetime_today)
						&&  ( (strtotime($date_get_date . ' ' . $time_startdate) >= strtotime($datetime_today))
							|| (strtotime($date_get_date . ' ' . $time_enddate) >= strtotime($datetime_today)) )
						 )
					{
						// Period with weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif ( !in_array($get_date, $perioddates)
						&& strtotime($get_date) >= strtotime($datetime_today) )
					{
						// Single Dates
						array_push($AllDatesFilterTime, $fD);
					}
				}
				// Filter Dates : Past Dates
				elseif ($filterTime == 2)
				{
					if ( in_array($get_date, $perioddates)
						&& $only_startdate
						&& (strtotime($EnDate) < strtotime($datetime_today))
						 )
					{
						// Period with no weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						&&  strtotime($get_date) < strtotime($datetime_today)
						&&  strtotime($date_get_date . ' ' . $time_enddate) < strtotime($datetime_today)
						 )
					{
						// Period with weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif ( !in_array($get_date, $perioddates)
						&& strtotime($get_date) < strtotime($datetime_today)
						 )
					{
						// Single Dates
						array_push($AllDatesFilterTime, $fD);
					}
				}
				// Filter Dates : Upcoming
				elseif ($filterTime == 3)
				{
					if (in_array($get_date, $perioddates)
						&& $only_startdate
						&& (strtotime($StDate) > strtotime($datetime_today))
						)
					{
						// Period with no weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif (in_array($get_date, $perioddates)
						&& !$only_startdate
						&&  strtotime($get_date) > strtotime($datetime_today)
						&&  strtotime($date_get_date . ' ' . $time_startdate) > strtotime($datetime_today)
						)
					{
						// Period with weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif (!in_array($get_date, $perioddates)
						&& strtotime($get_date) > strtotime($datetime_today)
						)
					{
						// Single Dates
						array_push($AllDatesFilterTime, $fD);
					}
				}
				// Filter Dates : Ongoing Events today
				elseif ($filterTime == 4)
				{
					if (in_array($get_date, $perioddates)
						&& $only_startdate
						&& strtotime($EnDate) > strtotime($datetime_today)
						&& strtotime($StDate) < (strtotime($date_today) + 86400)
						)
					{
						// Period with no weekdays selected
						array_push($AllDatesFilterTime, $date_get_date . ' ' . $time_startdate . '_' . $i_id);
					}
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						&& ( strtotime($get_date) == strtotime($date_today)
						&& strtotime($date_get_date . ' ' . $time_enddate) < (strtotime($date_today) + 86400) )
						 )
					{
						// Period with weekdays selected
						array_push($AllDatesFilterTime, $fD);
					}
					elseif ( !in_array($get_date, $perioddates)
						&& ( strtotime($get_date) >= strtotime($datetime_today)
						&& strtotime($get_date) < (strtotime($date_today) + 86400) )
						 )
					{
						// Single Dates
						array_push($AllDatesFilterTime, $fD);
					}
				}
			}

			$list_all_dates = array_merge($AllDatesFilterTime, $list_all_dates);
		}

		if ($orderby == 2)
		{
			sort($list_all_dates);
		}
		else
		{
			rsort($list_all_dates);
		}

		return $list_all_dates;
	}


	/**
	 * Fetch data from DB
	 */
	protected function getDBitems()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get Settings
		$selection_of_events	= $params->get('time', 1);
		$filterTime				= $params->get('time', '1');

		$layout = JRequest::getVar('layout', '');

		// Set vars
		$nodate			= '0000-00-00 00:00:00';
		$eventTimeZone	= null;
		$datetime_today	= JHtml::date('now', 'Y-m-d H:i:s'); // Joomla Time Zone
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone
		$time_today		= JHtml::date('now', 'H:i:s'); // Joomla Time Zone

		// Preparing connection to db
		$db	= Jfactory::getDbo();

		// Preparing the query
		$query = $db->getQuery(true);

		// START NEXT UPDATE
		$query->select('next AS tNext, dates AS tDates, startdate AS tStartdate, enddate AS tEnddate,
						weekdays AS tWeekdays, id AS tId, state AS tState, access AS tAccess');
		$query->from('`#__icagenda_events` AS e');
		$query->where(' e.state = 1 OR e.state = 0 ');
		$db->setQuery($query);

		$all_next_dates = $db->loadObjectList();

		foreach ($all_next_dates as $nd)
		{
			$nd_next		= $nd->tNext;
			$nd_id			= $nd->tId;
			$nd_state		= $nd->tState;
			$nd_dates		= $nd->tDates;
			$nd_startdate	= $nd->tStartdate;
			$nd_enddate		= $nd->tEnddate;
			$nd_weekdays	= $nd->tWeekdays;

			$dates = unserialize($nd_dates);

			$AllDates = array();

			// Get WeekDays Array
			$WeeksDays = iCDatePeriod::weekdaysToArray($nd_weekdays);

			// If Single Dates, added to all dates for this event (ADD FIX 3.1.11 !)
			$singledates = unserialize($nd_dates);

			if (isset ($dates)
				&& $dates != NULL
				&& !in_array($nodate, $singledates)
				&& !in_array('', $singledates)
				)
			{
				$AllDates = array_merge($AllDates, $dates);
			}
			elseif (in_array('', $singledates))
			{
				$datesarray		= array();
				$nodate			= array('0000-00-00 00:00');
				$datesmerger	= array_push($datesarray, $nodate);
				$DatesUpdate	= serialize($nodate);

				$query	= $db->getQuery(true);
				$query->update('#__icagenda_events');
				$query->set("`dates`='" . (string)$DatesUpdate . "'");
				$query->where('`id`=' . (int)$nd_id);
				$db->setQuery($query);
				$db->query($query);

				$nosingledates	= unserialize($DatesUpdate);
				$AllDates		= array_merge($AllDates, $nosingledates);
			}

			$StDate			= JHtml::date($nd_startdate, 'Y-m-d H:i', $eventTimeZone);
			$EnDate			= JHtml::date($nd_enddate, 'Y-m-d H:i', $eventTimeZone);

			$date_enddate	= JHtml::date($nd_enddate, 'Y-m-d', $eventTimeZone);
			$time_enddate	= JHtml::date($nd_enddate, 'H:i', $eventTimeZone);
			$date_startdate = JHtml::date($nd_startdate, 'Y-m-d', $eventTimeZone);
			$time_startdate = JHtml::date($nd_startdate, 'H:i', $eventTimeZone);

			$perioddates	= iCDatePeriod::listDates($nd_startdate, $nd_enddate, $eventTimeZone);

			$only_startdate	= ($nd_weekdays || $nd_weekdays == '0') ? false : true;

			if (isset($perioddates)
				&& $perioddates != NULL
				)
			{
				if ($only_startdate
					&& ($selection_of_events == '3' || $selection_of_events == '2')
					) // Period with no weekdays in Upcoming and Past options
				{
					array_push($AllDates, $StDate);
				}
				else
				{
					foreach ($perioddates as $Dat)
					{
						if (in_array(date('w', strtotime($Dat)), $WeeksDays))
						{
							$date_Dat = JHtml::date($Dat, 'Y-m-d', $eventTimeZone);
							$SingleDate = JHtml::date($Dat, 'Y-m-d H:i', $eventTimeZone);

							if ( $date_Dat == $date_today && $selection_of_events != 3 )
							{
								// Next in Period is today, so set end time
								array_push($AllDates, $date_Dat . ' ' .$time_startdate);
							}
							else
							{
								array_push($AllDates, $SingleDate);
							}
						}
					}
				}
			}

			rsort($AllDates);

			if ($AllDates == NULL)
			{
				$next ='0000-00-00 00:00:00';
			}
			else
			{
				$date_lastdate		= JHtml::date($AllDates[0], 'Y-m-d', $eventTimeZone);
				$datetime_lastdate	= JHtml::date($AllDates[0], 'Y-m-d H:i:s', $eventTimeZone);

				$date_startdate		= JHtml::date($nd_startdate, 'Y-m-d', $eventTimeZone);
				$date_enddate		= JHtml::date($nd_enddate, 'Y-m-d', $eventTimeZone);

				$time_startdate		= JHtml::date($nd_startdate, 'H:i:s', $eventTimeZone);
				$time_enddate		= JHtml::date($nd_enddate, 'H:i:s', $eventTimeZone);

				$returnNext			= $nd_next;

				foreach ($AllDates as $a)
				{
					$tsdatetime_a	= JHtml::date($a, 'Y-m-d H:i:s', $eventTimeZone);
					$tsdate_a		= JHtml::date($a, 'Y-m-d', $eventTimeZone);

					if ($datetime_lastdate < $datetime_today
						&& $date_lastdate != $date_today) // Past Event
					{
						$returnNext = JHtml::date($AllDates[0], 'Y-m-d H:i:s', $eventTimeZone);
					}
					elseif ($date_lastdate == $date_today) // Last Date is Today and still running
					{
						if ($nd_startdate != $nodate
							&& $nd_enddate != $nodate
							&& in_array($a, $perioddates)
							&& !$only_startdate
							)
						{
							// If Period
							$returnNext = JHtml::date($nd_enddate, 'Y-m-d', $eventTimeZone) . ' ' . $time_startdate;
						}
						elseif ($nd_startdate != $nodate
							&& $nd_enddate != $nodate
							&& in_array($a, $perioddates)
							&& $only_startdate
							)
						{
							// If Period
							$returnNext = JHtml::date($nd_startdate, 'Y-m-d', $eventTimeZone) . ' ' . $time_startdate;
						}
						else
						{
							$returnNext = JHtml::date($AllDates[0], 'Y-m-d H:i:s', $eventTimeZone);
						}
					}
					elseif ($tsdatetime_a > $datetime_today)
					{
						$returnNext = JHtml::date($a, 'Y-m-d H:i:s', $eventTimeZone);
					}
				}

				// Test End Date if Next Date or Last Date (3.1.5)
				$date_returnNext	= JHtml::date($returnNext, 'Y-m-d', $eventTimeZone);
				$time_returnNext	= JHtml::date($returnNext, 'H:i:s', $eventTimeZone);

				if ( ($date_enddate != '0000-00-00')
					&& ( $date_today == $date_enddate || $date_today == $date_returnNext) )
				{
					$time_LastTime = $time_startdate;
				}
				else
				{
					$time_LastTime = $time_returnNext;
				}

				// Fix 3.1.12 (removed isset($tPeriod))
				if ( ($nd_enddate != $nodate)
					&& ($date_startdate < $date_today)
					&& ($date_enddate == $date_today)
					&& ($time_LastTime >= $time_today) )
				{
					$returnNextPediod = JHtml::date($nd_enddate, 'Y-m-d', $eventTimeZone) . ' ' . $time_startdate;
				}
				else
				{
					$returnNextPediod = $returnNext;
				}

				// Set next var
				if ( ($date_returnNext == $date_enddate)
					&& ($date_enddate == $date_today) )
				{
					$next = $returnNextPediod;
				}
				elseif (strtotime($date_startdate) < strtotime($date_today)
					&& strtotime($date_enddate) >= strtotime($date_today)
					&& strtotime($time_enddate) != strtotime($time_returnNext)
					&& strtotime($time_LastTime) > strtotime($time_today)
					)
				{
					$next = $date_returnNext . ' ' . date('H:i:s', strtotime($time_LastTime));
				}
				else
				{
					$next = $returnNext;
				}
			}
			// 3.1.12 Fixed and update events with bug
			if ($nd_next == $nodate
				&& $nd_state == 0
				&& $nd_startdate != $nodate
				&& $nd_enddate != $nodate
				&& strtotime($nd_enddate) >= strtotime($nd_startdate)
				)
			{
				$next = $returnNext;

				$query	= $db->getQuery(true);
				$query->update('#__icagenda_events');
				$query->set('`state`=1');
				$query->where('`id`='.(int)$nd_id);
				$db->setQuery($query);
				$db->query($query);
			}

			if ($next != $nd_next)
			{
				$query	= $db->getQuery(true);
				$query->update('#__icagenda_events');
				$query->set("`next`='".$next."'");
				$query->where('`id`='.(int)$nd_id);
				$db->setQuery($query);
				$db->query($query);
			}
		}
		// END NEXT UPDATE

		// Get List Type option (list of events / list of dates)
		$allDatesDisplay = $this->options['datesDisplay'];

		// Preparing the query
		$query = $db->getQuery(true);

		// Selectable items
		$query->select('e.*,
			e.place as place_name, e.coordinate as coordinate, e.lat as lat, e.lng as lng,
			c.id as cat_id, c.title as cat_title, c.color as cat_color, c.desc as cat_desc, c.alias as cat_alias');

		// join
		$query->from('`#__icagenda_events` AS e');
		$query->leftJoin('`#__icagenda_category` AS c ON c.id = e.catid');
		$query->where('c.state = 1');

		// Where (filters)
		$filters = $this->filters;

		$where = 'e.state = ' . $filters['state'];

		$user = JFactory::getUser();
		$userLevels = $user->getAuthorisedViewLevels();

		// Joomla 3.x / 2.5 SWITCH
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$userGroups = $user->groups;
		}
		else
		{
			$userGroups = $user->getAuthorisedGroups();
		}

		$groupid = JComponentHelper::getParams('com_icagenda')->get('approvalGroups', array("8"));

		$groupid = is_array($groupid) ? $groupid : array($groupid);

		// Test if user login have Approval Rights
		if ( !array_intersect($userGroups, $groupid)
			&& !in_array('8', $userGroups) )
		{
			$where.= ' AND e.approval <> 1';
		}
		else
		{
			$where.= ' AND e.approval < 2';
		}

		// ACCESS Filtering (if not list, use layout access control (event, registration))
		if ( !$layout
			&& !in_array('8', $userGroups) )
		{
			$useraccess = implode(', ', $userLevels);

			$where.= ' AND e.access IN (' . $useraccess . ')';
		}

		// LANGUAGE Filtering
		$where.= ' AND (e.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . '))';

		unset($filters['state']);

		$k = '0';

		if (isset($filters))
		{
			foreach($filters as $k=>$v)
			{
				// normal cases
				if ($k != 'key' && $k != 'next' && $k != 'e.catid' && $k != 'id')
				{
					$where.= ' AND '.$k.' LIKE "%'.$v.'%"';
				}

				// in case of search
				if ($k == 'key')
				{
					$keys = explode(' ', $v);

					foreach ($keys as $ke)
					{
						$where.= ' AND (e.title LIKE \'%' . $ke . '%\' OR ';
						$where.= ' e.desc LIKE \'%' . $ke . '%\' OR ';
						$where.= ' e.address LIKE \'%' . $ke . '%\' OR ';
						$where.= ' e.place LIKE \'%' . $ke . '%\' OR ';
						$where.= ' c.title LIKE \'%' . $ke . '%\')';
					}
				}

				// in the case of category
				$mcatidtrue = $this->options['mcatid'];

				if (!is_array($mcatidtrue))
				{
					$catold = $mcatidtrue;
					$mcatid = array($mcatidtrue);
				}
				else
				{
					$catold = '0';
					$mcatid = $mcatidtrue;
				}

				if ( !in_array('0', $mcatid)
					|| ($catold != 0) )
				{
					if ($k == 'e.catid')
					{
						if (!is_array($v))
						{
							$v = array('' . $v . '');
						}

						$v = implode(', ', $v);

						$where.= ' AND ' . $k . ' IN (' . $v . ')';
					}
				}

				// in case of id
				if ($k == 'id')
				{
					//check if ID is a number
					if (is_numeric($v))
					{
						$where.= ' AND e.id=' . $v;
					}
					else
					{
						//ERROR Message
					}
				}
			}
		}

		// Features - extract the number of displayable icons per event
		$query->select('feat.count AS features');
		$sub_query = $db->getQuery(true);
		$sub_query->select('fx.event_id, COUNT(*) AS count');
		$sub_query->from('`#__icagenda_feature_xref` AS fx');
		$sub_query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
		$sub_query->group('fx.event_id');
		$query->leftJoin('(' . (string) $sub_query . ') AS feat ON e.id=feat.event_id');

		// Filter by Features
		if (!$layout) // if view is list of events (temporary fix for calendar event links to details view)
		{
			$query->where(iCModeliChelper::getFeaturesFilter());
		}

		// Registrations total
		$query->select('r.count AS registered');
		$sub_query = $db->getQuery(true);
		$sub_query->select('r.eventid, sum(r.people) AS count');
		$sub_query->from('`#__icagenda_registration` AS r');
		$sub_query->where('r.state > 0');
		$sub_query->group('r.eventid');
		$query->leftJoin('(' . (string) $sub_query . ') AS r ON e.id=r.eventid');

		if (!$layout)
		{
			$number_per_page	= $this->options['number'];
			$orderdate			= $this->options['orderby'];
			$getpage			= JRequest::getVar('page', '1');

			$start = $number_per_page * ($getpage - 1);

			$all_dates_with_id	= self::getAllDates();

			$count_all_dates	= count($all_dates_with_id);

			// Set list of PAGE:IDS
			$pages = ceil($count_all_dates / $number_per_page);
			$list_id = array();

			for ($n = 1; $n <= $pages; $n++)
			{
				$dpp_array = array();

				$page_nb		= $number_per_page * ($n - 1);
				$dates_per_page	= array_slice($all_dates_with_id, $page_nb, $number_per_page, true);

				foreach ($dates_per_page AS $dpp)
				{
					$dpp_alldates_array	= explode('_', $dpp);
					$dpp_date			= $dpp_alldates_array['0'];
					$dpp_id				= $dpp_alldates_array['1'];
					$dpp_array[]		= $dpp_id;
				}

				$list_id[] = implode(', ', $dpp_array) . '::' . $n;
			}

			$this_ic_ids = '';

			if ($list_id)
			{
				foreach ($list_id as $a)
				{
					$ex_listid = explode('::', $a);
					$ic_page = $ex_listid[1];
					$ic_ids = $ex_listid[0];

					if ($ic_page == $getpage)
					{
						$this_ic_ids = $ic_ids ? $ic_ids : '0';
					}
				}

				if ($this_ic_ids)
				{
					$where.= ' AND (e.id IN (' . $this_ic_ids . '))';
				}
				else
				{
					return false; // No Event (if 'All Dates' option selected)
				}
			}
		}

		// Query $where list
		$query->where($where);

		$db->setQuery($query);
		$loaddb = $db->loadObjectList();

		// Extract the feature details, if needed
		foreach ($loaddb as $record)
		{
			if (is_null($record->features))
			{
				$record->features = array();
			}
			else
			{
				$query = $db->getQuery(true);
				$query->select('DISTINCT f.icon, f.icon_alt');
				$query->from('`#__icagenda_feature_xref` AS fx');
				$query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
				$query->where('fx.event_id=' . $record->id);
				$query->order('f.ordering DESC'); // Order descending because the icons are floated right
				$db->setQuery($query);
				$record->features = $db->loadObjectList();
			}
		}

		if ((!$layout && count($all_dates_with_id) > 0)
			|| $layout)
		{
			return $loaddb;
		}
	}


	/**
	 *
	 * ALL DATES - iCmodel
	 *
	 */
	protected function AllDates ($i)
	{
		// Set vars
		$nodate = '0000-00-00 00:00:00';
		$ic_nodate = '0000-00-00 00:00';
		$eventTimeZone = null;

		// Get Data
		$tNext			= $i->next;
		$tDates			= $i->dates;
		$tId			= $i->id;
		$tState			= $i->state;
		$tEnddate		= $i->enddate;
		$tStartdate		= $i->startdate;
		$tWeekdays		= $i->weekdays;

		// Declare AllDates array
		$AllDates = array();

		// Get WeekDays Array
		$WeeksDays = iCDatePeriod::weekdaysToArray($tWeekdays);

		// If Single Dates, added each one to All Dates for this event
		$singledates = unserialize($tDates);

		foreach ($singledates as $sd)
		{
			$isValid = iCDate::isDate($sd);

			if ( $isValid )
			{
				array_push($AllDates, $sd);
			}
		}

		// If Period Dates, added each one to All Dates for this event (filter week Days, and if date not null)
		$StDate = JHtml::date($tStartdate, 'Y-m-d H:i', $eventTimeZone);
		$EnDate = JHtml::date($tEnddate, 'Y-m-d H:i', $eventTimeZone);

		$perioddates = iCDatePeriod::listDates($StDate, $EnDate, $eventTimeZone);

		if ( (isset ($perioddates))
			&& ($perioddates != NULL) )
		{
			foreach ($perioddates as $Dat)
			{
				if (in_array(date('w', strtotime($Dat)), $WeeksDays))
				{
					$isValid = iCDate::isDate($Dat);

					if ($isValid)
					{
						$SingleDate = JHtml::date($Dat, 'Y-m-d H:i', $eventTimeZone);

						array_push($AllDates, $SingleDate);
					}
				}
			}
		}

		return $AllDates;
	}

	/**
	 *
	 * EVENT DETAILS
	 *
	 */

	protected function access_event($i)
	{
		return $i->access;
	}
	protected function startdatetime($i)
	{
		return $i->startdate;
	}
	protected function enddatetime($i)
	{
		return $i->enddate;
	}
	protected function start_datetime($i)
	{
		return $i->startdate;
	}
	protected function end_datetime($i)
	{
		return $i->enddate;
	}
	protected function contact_name($i)
	{
		return $i->name;
	}
	protected function contact_email($i)
	{
		return $i->email;
	}

	protected function state($i){return $i->state;}
	protected function approval($i){return $i->approval;}
	protected function displaytime($i){return $i->displaytime;}
	protected function cat_desc($i){return $i->cat_desc;}
	protected function period($i){return $i->period;}
	protected function access($i){return $i->access;}
	protected function place_name($i){return $i->place_name;}
	protected function address($i){return $i->address;}
	protected function phone($i){return $i->phone;}
	protected function dates($i){return $i->dates;}
	protected function weekdays($i){return $i->weekdays;}
	protected function email($i){return $i->email;}
	protected function website($i){return $i->website;}
	protected function city($i){return $i->city;}
	protected function country($i){return $i->country;}
	protected function file($i){return $i->file;}
	protected function customfields($i){return $i->customfields;}


	// Set Meta-title for an event
	protected function metaTitle($i)
	{
		$limit = '70';
		$metaTitle = iCFilterOutput::fullCleanHTML($i->title);

		if ( strlen($metaTitle) > $limit )
		{
			$string_cut	= substr($metaTitle, 0, $limit);
			$last_space	= strrpos($string_cut, ' ');
			$string_ok	= substr($string_cut, 0, $last_space);
			$metaTitle = $string_ok;
		}

		return $metaTitle;
	}

	// Set Meta-description for an event
	protected function metaDesc($i)
	{
		$limit = '160';
		$metaDesc = iCFilterOutput::fullCleanHTML($i->metadesc);

		if ( empty($metaDesc) )
		{
			$metaDesc = iCFilterOutput::fullCleanHTML($i->desc);
		}

		if ( strlen($metaDesc) > $limit )
		{
			$string_cut	= substr($metaDesc, 0, $limit);
			$last_space	= strrpos($string_cut, ' ');
			$string_ok	= substr($string_cut, 0, $last_space);
			$metaDesc = $string_ok;
		}

		return $metaDesc;
	}

	// Set Meta-description as Short Description
	protected function metaAsShortDesc($i)
	{
		$metaAsShortDesc = iCFilterOutput::fullCleanHTML($i->metadesc);

		return $metaAsShortDesc;
	}


	protected function BackURL($i)
	{
		// Get Current Itemid
		//$this_itemid = JRequest::getInt('Itemid');

		//$BackURL = str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&view=list&Itemid='.$this_itemid));
		$BackURL = 'javascript:history.go(-1)';

		return $BackURL;
	}

	protected function BackArrow($i)
	{
		// Get Current Itemid
		$this_itemid	= JRequest::getInt('Itemid');

		$layout			= JRequest::getVar('layout', '');
		$manageraction	= JRequest::getVar('manageraction', '');
		$referer		= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

		// RTL css if site language is RTL
		$lang			= JFactory::getLanguage();
		$back_icon		= ($lang->isRTL()) ? 'iCicon iCicon-nextic' : 'iCicon iCicon-backic';

		if ($layout != ''
			&& strpos($referer,'registration') === false
			&& !$manageraction)
		{
			if ($referer != "")
			{
				$BackArrow = '<a class="iCtip" href="'. str_replace(array('"', '<', '>', "'"), '', $referer) .'" title="'. JText::_( 'COM_ICAGENDA_BACK' ) .'"><span aria-hidden="true" class="' . $back_icon . '"></span> <span class="small">'. JText::_( 'COM_ICAGENDA_BACK' ) .'</span></a>';
			}
			else
			{
				$BackArrow = '';
				return false;
			}
		}
		elseif ($manageraction || strpos($referer,'registration') !== false)
		{
			$BackArrow = '<a class="iCtip" href="'. str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&Itemid='.$this_itemid)) .'" title="'. JText::_( 'COM_ICAGENDA_BACK' ) .'"><span aria-hidden="true" class="' . $back_icon . '"></span> <span class="small">'. JText::_( 'COM_ICAGENDA_BACK' ) .'</span></a>';
		}
		else
		{
			return false;
		}

		return $BackArrow;
	}


	protected function ApprovedNotification ($creatorEmail, $eventUsername, $eventTitle, $eventLink)
	{
		// Load Joomla Config
		$config = JFactory::getConfig();

		// Get the site name / Global Joomla Contact Infos
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$sitename = $config->get('sitename');
			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');
		}
		else
		{
			$sitename = $config->getValue('config.sitename');
			$mailfrom = $config->getValue('config.mailfrom');
			$fromname = $config->getValue('config.fromname');
		}

		// Create User Mailer
		$approvedmailer = JFactory::getMailer();

		// Set Sender of Notification Email
		$approvedmailer->setSender(array( $mailfrom, $fromname ));

		// Set Recipient of Notification Email
		$approvedmailer->addRecipient($creatorEmail);

		// Set Subject of Notification Email
		$approvedsubject = JText::sprintf('COM_ICAGENDA_APPROVED_USEREMAIL_SUBJECT', $eventTitle);
		$approvedmailer->setSubject($approvedsubject);

		// Set Body of Notification Email
		$approvedbodycontent = JText::sprintf( 'COM_ICAGENDA_SUBMISSION_ADMIN_EMAIL_HELLO', $eventUsername) . ',<br /><br />';
		$approvedbodycontent.= JText::sprintf( 'COM_ICAGENDA_APPROVED_USEREMAIL_BODY_INTRO', $sitename) . '<br /><br />';
//		$approvedbodycontent.= JText::_( 'COM_ICAGENDA_APPROVED_USEREMAIL_EVENT_LINK' ).'<br />';

		$eventLink_html = '<br /><a href="' . $eventLink . '">' . $eventLink . '</a>';
		$approvedbodycontent.= JText::sprintf( 'COM_ICAGENDA_APPROVED_USEREMAIL_EVENT_LINK', $eventLink_html ).'<br /><br />';

//		$approvedbodycontent.= '<a href="' . $eventLink . '">' . $eventLink . '</a><br /><br />';
		$approvedbodycontent.= '<hr><small>' . JText::_( 'COM_ICAGENDA_APPROVED_USEREMAIL_EVENT_LINK_INFO' ) . '</small><br /><br />';

		$approvedbody = rtrim($approvedbodycontent);

		$approvedmailer->isHTML(true);
		$approvedmailer->Encoding = 'base64';

		$approvedmailer->setBody($approvedbody);

		// Send User Notification Email
		if (isset($creatorEmail))
		{
			$send = $approvedmailer->Send();
		}
	}

	protected function ManagerIcons ($i)
	{
		$app = JFactory::getApplication();

		// Get Current Itemid
		$this_itemid = JRequest::getInt('Itemid');

		// Get Current Url
		$returnURL = base64_encode(JURI::getInstance()->toString());

		$event_slug = empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;

		// Set Manager Actions Url
		$managerActionsURL = 'index.php?option=com_icagenda&view=list&layout=event&id=' . $event_slug . '&Itemid=' . $this_itemid;

		// Set Email Notification Url to event
		$linkEmailUrl = JURI::base() . 'index.php?option=com_icagenda&view=list&layout=event&id=' . $event_slug . '&Itemid=' . $this_itemid;

		// Get Approval Status
		$approved = $i->approval;

		// Get User groups allowed to approve event submitted
		$groupid = JComponentHelper::getParams('com_icagenda')->get('approvalGroups', array("8"));

		$groupid = is_array($groupid) ? $groupid : array($groupid);

		// Get User Infos
		$user	= JFactory::getUser();

		$icid	= $user->get('id');
		$icu	= $user->get('username');
		$icp	= $user->get('password');

		// Get User groups of the user logged-in
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$userGroups = $user->getAuthorisedGroups();
		}
		else
		{
			$userGroups = $user->groups;
		}

		$baseURL = JURI::base();
		$subpathURL = JURI::base(true);

		$baseURL = str_replace('/administrator', '', $baseURL);
		$subpathURL = str_replace('/administrator', '', $subpathURL);

		$urlcheck = str_replace('&amp;','&', JRoute::_('administrator/index.php?option=com_icagenda&view=events').'&icu=' . $icu . '&icp=' . $icp . '&filter_search=' . $i->id);

		// Sub Path filtering
		$subpathURL = ltrim($subpathURL, '/');

		// URL Event Check filtering
		$urlcheck = ltrim($urlcheck, '/');

		if (substr($urlcheck, 0, strlen($subpathURL)+1) == "$subpathURL/")
		{
			$urlcheck = substr($urlcheck, strlen($subpathURL)+1);
		}

		$urlcheck = rtrim($baseURL, '/') . '/' . ltrim($urlcheck, '/');

		$icu_approve = JRequest::getVar('manageraction', '');
		$icu_layout = JRequest::getVar('layout', '');

		if ( array_intersect($userGroups, $groupid)
			|| in_array('8', $userGroups) )
		{
			if ($approved == 1)
			{
				if (version_compare(JVERSION, '3.0', 'lt'))
				{
					$approvalButton = '<a class="iCtip" href="'.JRoute::_($managerActionsURL.'&manageraction=approve').'" title="'.JText::_( 'COM_ICAGENDA_APPROVE_AN_EVENT_LBL' ).'"><div class="iCicon-16 approval"></div></a>';
 				}
 				else
 				{
					$approvalButton = '<a class="iCtip" href="'.JRoute::_($managerActionsURL.'&manageraction=approve').'" title="'.JText::_( 'COM_ICAGENDA_APPROVE_AN_EVENT_LBL' ).'"><button type="button" class="btn btn-micro btn-warning btn-xs"><i class="icon-checkmark"></i></button></a>';
				}

				if ( ($icu_layout == 'event')
					&& ($icu_approve == 'approve') )
				{
        			$db		= Jfactory::getDbo();
					$query	= $db->getQuery(true);
        			$query->clear();
					$query->update(' #__icagenda_events ');
					$query->set(' approval = 0 ' );
					$query->where(' id = ' . (int) $i->id );
					$db->setQuery((string)$query);
					$db->query($query);
					$approveSuccess = '"'.$i->title.'"';
					$alertmsg = JText::sprintf('COM_ICAGENDA_APPROVED_SUCCESS', $approveSuccess);
					$alerttitle = JText::_( 'COM_ICAGENDA_APPROVED' );
					$alerttype = 'success';
					$approvedLink = JRoute::_($managerActionsURL);

					self::ApprovedNotification($i->created_by_email, $i->username, $i->title, $linkEmailUrl);
					$app->enqueueMessage($alertmsg, $alerttitle, $alerttype);
				}
				else
				{
					return $approvalButton;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	// Function Email Cloaking
	protected function emailLink ($i)
	{
		if ($i->email != NULL)
		{
			return JHtml::_('email.cloak', $i->email);
		}
	}

	// Image URL
	protected function image ($i)
	{
		$ic_image = JURI::base() . $i->image;

		if ($i->image)
		{
			return $ic_image;
		}

		return false;
	}


	// Get Items
	protected function items($atr)
	{
		// Initialize controls
		$access = '0';
		$control = '';

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.title, a.published, a.id')
			->from('`#__menu` AS a')
			->where( "(link = 'index.php?option=com_icagenda&view=list') AND (published > 0)" );
		$db->setQuery($query);
		$link = $db->loadObjectList();
		$itemid = JRequest::getVar('Itemid');

		$parentnav = $itemid;

		foreach ($link as $l)
		{
			if (($l->published == '1') AND ($l->id == $parentnav))
			{
				$linkexist = '1';
			}
		}

		if (is_numeric($parentnav) && !is_array($parentnav) && !$parentnav == 0 && $linkexist == 1)
		{
			$atr	= $atr['item'];
			$items	= $this->items;
			$itDef	= new stdClass;

			if ($this->items == NULL)
			{
				return NULL;
			}
			else
			{
				foreach($items as $i)
				{
					// Language Control
					$lang = JFactory::getLanguage();
					$eventLang = '';
					$langTag = '';
					$langTag = $lang->getTag();

					if (isset($i->language))
					{
						$eventLang = $i->language;
					}
					if ($eventLang == '' || $eventLang == '*')
					{
						$eventLang = $langTag;
					}

					if ($i->next != '0000-00-00 00:00:00')
					{
						$it	= new stdClass;
						$id	= $i->id;

						foreach($atr as $k => $v)
						{
							// Corrige Notice : Undefined property: stdClass::
							if (!empty($i->$k))
							{
								// functions
								$it->$k = $i->$k;
							}
							else
							{
								// data
								if (method_exists($this, $k))
								{
									$it->$k = $this->$k($i);
								}
							}
						}
						$itDef->$id = $it;
					}
				}
			}

			return $itDef;
		}
		else
		{
			JError::raiseError('404', JTEXT::_('JERROR_LAYOUT_PAGE_NOT_FOUND'));

			return false;
		}
	}


	// Get event Url
	protected function url ($i)
	{
		$menuID			= $this->options['Itemid'];
		$eventnumber	= $i->id;
		$event_slug		= empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;

		$url			= JRoute::_('index.php?option=com_icagenda&view=list&layout=event&id=' . $event_slug . '&Itemid=' . (int)$menuID);

		if (is_numeric($menuID) && is_numeric($eventnumber)
			&& !is_array($menuID) && !is_array($eventnumber)
			)
		{
			return $url;
		}
		else
		{
			$url = JRoute::_('index.php');

			return $url;
		}
	}

	// Get event Url
	protected function Event_Link ($i)
	{
		$lien			= $this->options['Itemid'];
		$eventnumber	= $i->id;
		$event_slug		= empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;
		$date			= $i->next;

		// Get the "event" URL
		$baseURL	= JURI::base();
		$subpathURL	= JURI::base(true);

		$baseURL	= str_replace('/administrator', '', $baseURL);
		$subpathURL	= str_replace('/administrator', '', $subpathURL);

		$urlevent	= str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&view=list&layout=event&Itemid=' . (int)$lien . '&id=' . $event_slug));

		// Sub Path filtering
		$subpathURL	= ltrim($subpathURL, '/');

		// URL Event Details filtering
		$urlevent	= ltrim($urlevent, '/');

		if (substr($urlevent, 0, strlen($subpathURL)+1) == "$subpathURL/")
		{
			$urlevent = substr($urlevent, strlen($subpathURL)+1);
		}

		$urlevent	= rtrim($baseURL,'/').'/'.ltrim($urlevent,'/');

		$url		= $urlevent;

		if (is_numeric($lien) && is_numeric($eventnumber)
			&& !is_array($lien) && !is_array($eventnumber)
			)
		{
			return $url;
		}
		else
		{
			$url = JRoute::_('index.php');

			return JURI::base().$url;
		}

	}

	// Title with link to details
	protected function titleLink($i)
	{
		return '<a href="' . $this->url($i) . '">' . $i->title . '</a>';
	}

	// Title + Manager Icons
	protected function titlebar ($i)
	{
		$this_itemid		= JRequest::getInt('Itemid');
		$list_title_length	= JComponentHelper::getParams('com_icagenda')->get('list_title_length', '');
		$layout				= JRequest::getVar('layout', '');
		$mbString			= extension_loaded('mbstring');

		$title_length		= $mbString ? mb_strlen($i->title, 'UTF-8') : strlen($i->title);

		if (empty($layout)
			&& !empty($list_title_length)
			)
		{
			$title	= $mbString
					? trim(mb_substr($i->title, 0, $list_title_length, 'UTF-8'))
					: trim(substr($i->title, 0, $list_title_length));

			$new_title_length = $mbString ? mb_strlen($title, 'UTF-8') : strlen($title);

			if ($new_title_length < $title_length)
			{
				$title.= '...';
			}
		}
		else
		{
			$title = $i->title;
		}

		$approval = $i->approval;

		$event_slug = empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;

		// Set Manager Actions Url
		$managerActionsURL	= 'index.php?option=com_icagenda&view=list&layout=event&id=' . $event_slug . '&Itemid=' . $this_itemid;

		$unapproved			= '<a class="iCtip" href="' . JRoute::_($managerActionsURL) . '" title="'.JText::_( 'COM_ICAGENDA_APPROVE_AN_EVENT_LBL' ).'"><small><span class="iCicon-open-details"></span></small></a>';

		if ($title != NULL && $approval == 1)
		{
			return $title.' '.$unapproved;
		}
		elseif ($title != NULL && $approval != 1)
		{
			return $title;
		}
		else
		{
			return NULL;
		}
	}

	// Title
	protected function title($i)
	{
		$title = ($i->title != NULL) ? $i->title : NULL;

		return $title;
	}

	// Short Description
	public function shortdesc($i)
	{
		$shortdesc = $i->shortdesc ? $i->shortdesc : NULL;

		return $shortdesc;
	}

	// Description
	public function desc($i)
	{
		$desc = $i->desc ? $i->desc : NULL;

		return $desc;
	}

	// Short Description (content prepare)
	protected function shortDescription($i)
	{
		$text				= JHtml::_('content.prepare', $i->shortdesc);
		$shortDescription	= $i->shortdesc ? $text : NULL;

		return $shortDescription;
	}

	// Full Description (content prepare)
	protected function description($i)
	{
		$text			= JHtml::_('content.prepare', $i->desc);
		$description	= $i->desc ? $text : NULL;

		return $description;
	}

	// Auto Short Description (Full Description > Short)
	protected function descShort($i)
	{
		$limitGlobal = $this->options['limitGlobal'];
		$customlimit = $this->options['limit'];

		if ($limitGlobal == 1)
		{
			$limit = JComponentHelper::getParams('com_icagenda')->get('ShortDescLimit', '100');
		}
		else
		{
			$limit_global_option = JComponentHelper::getParams('com_icagenda')->get('ShortDescLimit', '100');
			$limit = is_numeric($customlimit) ? $customlimit : $limit_global_option;
		}

		// Html tags removal Global Option (component iCagenda) - Short Description
		$Filtering_ShortDesc_Global	= JComponentHelper::getParams('com_icagenda')->get('Filtering_ShortDesc_Global', '');
		$HTMLTags_ShortDesc_Global	= JComponentHelper::getParams('com_icagenda')->get('HTMLTags_ShortDesc_Global', array());

		/**
		 * START Filtering HTML method
		 */
		$limit				= is_numeric($limit) ? $limit : false;
		$desc_full			= $i->desc;

		// Gets length of the short desc, when not filtered
		$limit_not_filtered	= substr($desc_full, 0, $limit);
		$text_length		= strlen($limit_not_filtered);

		// Gets length of the short desc, after html filtering
		$limit_filtered		= preg_replace('/[\p{Z}\s]{2,}/u', ' ', $limit_not_filtered);
		$limit_filtered		= strip_tags($limit_filtered);
		$text_short_length	= strlen($limit_filtered);

		// Sets Limit + special tags authorized
		$limit_short		= $limit + ($text_length - $text_short_length);

		// Replaces all authorized html tags with tag strings
		if ($Filtering_ShortDesc_Global == '1')
		{
			$desc_full = str_replace('+', '@@', $desc_full);
			$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br>', '+@br@', $desc_full) : $desc_full;
			$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br/>', '+@br@', $desc_full) : $desc_full;
			$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br />', '+@br@', $desc_full) : $desc_full;
			$desc_full = in_array('2', $HTMLTags_ShortDesc_Global) ? str_replace('<b>', '+@b@', $desc_full) : $desc_full;
			$desc_full = in_array('2', $HTMLTags_ShortDesc_Global) ? str_replace('</b>', '@bc@', $desc_full) : $desc_full;
			$desc_full = in_array('3', $HTMLTags_ShortDesc_Global) ? str_replace('<strong>', '@strong@', $desc_full) : $desc_full;
			$desc_full = in_array('3', $HTMLTags_ShortDesc_Global) ? str_replace('</strong>', '@strongc@', $desc_full) : $desc_full;
			$desc_full = in_array('4', $HTMLTags_ShortDesc_Global) ? str_replace('<i>', '@i@', $desc_full) : $desc_full;
			$desc_full = in_array('4', $HTMLTags_ShortDesc_Global) ? str_replace('</i>', '@ic@', $desc_full) : $desc_full;
			$desc_full = in_array('5', $HTMLTags_ShortDesc_Global) ? str_replace('<em>', '@em@', $desc_full) : $desc_full;
			$desc_full = in_array('5', $HTMLTags_ShortDesc_Global) ? str_replace('</em>', '@emc@', $desc_full) : $desc_full;
			$desc_full = in_array('6', $HTMLTags_ShortDesc_Global) ? str_replace('<u>', '@u@', $desc_full) : $desc_full;
			$desc_full = in_array('6', $HTMLTags_ShortDesc_Global) ? str_replace('</u>', '@uc@', $desc_full) : $desc_full;
		}
		elseif ($Filtering_ShortDesc_Global == '')
		{
			$desc_full		= '@i@'.$desc_full.'@ic@';
			$limit_short	= $limit_short + 7;
		}
		else
		{
			$desc_full		= $desc_full;
		}

		// Removes HTML tags
		$desc_nohtml	= strip_tags($desc_full);

		// Replaces all sequences of two or more spaces, tabs, and/or line breaks with a single space
		$desc_nohtml	= preg_replace('/[\p{Z}\s]{2,}/u', ' ', $desc_nohtml);

		// Replaces all spaces with a single +
		$desc_nohtml	= str_replace(' ', '+', $desc_nohtml);

		if (strlen($desc_nohtml) > $limit_short)
		{
			// Cuts full description, to get short description
			$string_cut	= substr($desc_nohtml, 0, $limit_short);

			// Detects last space of the short description
			$last_space	= strrpos($string_cut, '+');

			// Cuts the short description after last space
			$string_ok	= substr($string_cut, 0, $last_space);

			// Counts number of tags converted to string, and returns lenght
			$nb_br			= substr_count($string_ok, '+@br@');
			$nb_plus		= substr_count($string_ok, '@@');
			$nb_bopen		= substr_count($string_ok, '@b@');
			$nb_bclose		= substr_count($string_ok, '@bc@');
			$nb_strongopen	= substr_count($string_ok, '@strong@');
			$nb_strongclose	= substr_count($string_ok, '@strongc@');
			$nb_iopen		= substr_count($string_ok, '@i@');
			$nb_iclose		= substr_count($string_ok, '@ic@');
			$nb_emopen		= substr_count($string_ok, '@em@');
			$nb_emclose		= substr_count($string_ok, '@emc@');
			$nb_uopen		= substr_count($string_ok, '@u@');
			$nb_uclose		= substr_count($string_ok, '@uc@');

			// Replaces tag strings with html tags
			$string_ok	= str_replace('@br@', '<br />', $string_ok);
			$string_ok	= str_replace('@b@', '<b>', $string_ok);
			$string_ok	= str_replace('@bc@', '</b>', $string_ok);
			$string_ok	= str_replace('@strong@', '<strong>', $string_ok);
			$string_ok	= str_replace('@strongc@', '</strong>', $string_ok);
			$string_ok	= str_replace('@i@', '<i>', $string_ok);
			$string_ok	= str_replace('@ic@', '</i>', $string_ok);
			$string_ok	= str_replace('@em@', '<em>', $string_ok);
			$string_ok	= str_replace('@emc@', '</em>', $string_ok);
			$string_ok	= str_replace('@u@', '<u>', $string_ok);
			$string_ok	= str_replace('@uc@', '</u>', $string_ok);
			$string_ok	= str_replace('+', ' ', $string_ok);
			$string_ok	= str_replace('@@', '+', $string_ok);

			$text = $string_ok;

			// Close html tags if not closed
			if ($nb_bclose < $nb_bopen) $text = $string_ok.'</b>';
			if ($nb_strongclose < $nb_strongopen) $text = $string_ok.'</strong>';
			if ($nb_iclose < $nb_iopen) $text = $string_ok.'</i>';
			if ($nb_emclose < $nb_emopen) $text = $string_ok.'</em>';
			if ($nb_uclose < $nb_uopen) $text = $string_ok.'</u>';

			$return_text = $text.' ';

			$descShort	= $limit ? $return_text : '';
		}
		else
		{
			$desc_full	= $desc_nohtml;
			$desc_full	= str_replace('@br@', '<br />', $desc_full);
			$desc_full	= str_replace('@b@', '<b>', $desc_full);
			$desc_full	= str_replace('@bc@', '</b>', $desc_full);
			$desc_full	= str_replace('@strong@', '<strong>', $desc_full);
			$desc_full	= str_replace('@strongc@', '</strong>', $desc_full);
			$desc_full	= str_replace('@i@', '<i>', $desc_full);
			$desc_full	= str_replace('@ic@', '</i>', $desc_full);
			$desc_full	= str_replace('@em@', '<em>', $desc_full);
			$desc_full	= str_replace('@emc@', '</em>', $desc_full);
			$desc_full	= str_replace('@u@', '<u>', $desc_full);
			$desc_full	= str_replace('@uc@', '</u>', $desc_full);
			$desc_full	= str_replace('+', ' ', $desc_full);
			$desc_full	= str_replace('@@', '+', $desc_full);

			$descShort	= $limit ? $desc_full : '';
		}
		/** END Filtering HTML function */

		return $descShort;
	}


	// Image TAG
	protected function imageTag($i)
	{
		if (!$i->image == NULL)
		{
			return '<img src="' . $i->image . '" alt="" />';
		}
	}


	// File TAG
	protected function fileTag($i)
	{
		return '<a class="icDownload" href="' . $i->file . '" target="_blank">' . JText::_( 'COM_ICAGENDA_EVENT_DOWNLOAD' ) . '</a>';
	}


	// Website TAG
	protected function websiteLink($i)
	{
		$gettarget	= JComponentHelper::getParams('com_icagenda')->get('targetLink', '');
		$target		= !empty($gettarget) ? '_blank' : '_parent';

		$link		= iCUrl::urlParsed($i->website, 'scheme');

		return '<a href="' . $link . '" target="' . $target . '">' . $i->website . '</a>';
	}


	protected function pastDates($i)
	{
		$dates			= unserialize($i->dates);
		$period			= unserialize($i->period);
		$eventTimeZone	= null;
		$datetime_today	= JHtml::date('now', 'Y-m-d H:i'); // Joomla Time Zone
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

		$alldates	= ($period != NULL) ? array_merge($dates, $period) : $dates;

		rsort($alldates);

		$lastDate	= JHtml::date($alldates[0], 'Y-m-d H:i', $eventTimeZone);

		if (strtotime($lastDate) < strtotime($datetime_today))
		{
			$pastDates = '0';
		}
		else
		{
			$pastDates = '1';
		}

		return $pastDates;
	}



	/**
	 * TIME
	 */

	// Format Time (eg. 00:00)
	protected function evenTime($i)
	{
		if ($this->displaytime($i) == 1)
		{
			return icagendaEvents::dateToTimeFormat($i->next);
		}
		else
		{
			return NULL;
		}
	}


	/**
	 * DAY
	 */

	// Day
	protected function day ($i)
	{
		$eventTimeZone	= null;
		$day_date		= JHtml::date($i->next, 'd', $eventTimeZone);

		return $day_date;
	}

	// Day of the week, Full - From Joomla language file xx-XX.ini (eg. Saturday)
	protected function weekday ($i)
	{
		$eventTimeZone	= null;
		$full_weekday	= JHtml::date($i->next, 'l', $eventTimeZone);
		$weekday		= JText::_($full_weekday);

		return $weekday;
	}

	// Day of the week, Short - From Joomla language file xx-XX.ini (eg. Sat)
	protected function weekdayShort ($i)
	{
		$eventTimeZone	= null;
		$short_weekday	= JHtml::date($i->next, 'D', $eventTimeZone);
		$weekdayShort	= JText::_($short_weekday);

		return $weekdayShort;
	}


	/**
	 * MONTHS
	 */

	// Function used for special characters
	function substr_unicode($str, $s, $l = null)
	{
    	return join("", array_slice(
		preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
	}

	// Format Month (eg. December)
	protected function month ($i)
	{
		$eventTimeZone	= null;
		$full_month		= JHtml::date($i->next, 'F', $eventTimeZone);
		$lang_month		= JText::_($full_month);

		return $lang_month;
	}

	// Format Month Short - 3 first characters (eg. Dec.) OR Joomla Translation core
	protected function monthShort ($i)
	{
		$Jcore = '1';
		$eventTimeZone	= null;
		$full_month		= JHtml::date($i->next, 'F', $eventTimeZone);

		if ($Jcore == '1')
		{
			return $this->monthShortJoomla($i);
		}
		else
		{
			$lang_month 	= JText::_($full_month);
			$monthShort 	= $this->substr_unicode($lang_month, 0, 3);
			$space = $point = '';

			if (strlen($full_month) > 3)
			{
				$space = '&nbsp;';
				$point = '.';
			}

			return $space.$monthShort.$point;
		}
	}

	// Format Month Short Core - From Joomla language file xx-XX.ini (eg. Dec)
	protected function monthShortJoomla ($i)
	{
		$eventTimeZone		= null;
		$next_tz			= JHtml::date($i->next, 'F', $eventTimeZone);
		$full_month			= date('F', strtotime($next_tz));
		$monthShortJoomla	= JText::_($full_month.'_SHORT');

		return $monthShortJoomla;
	}

	// Format Month Numeric - (eg. 07)
	protected function monthNum ($i)
	{
		$eventTimeZone	= null;
		$monthNum		= JHtml::date($i->next, 'm', $eventTimeZone);

		return $monthNum;
	}


	/**
	 * YEAR
	 */

	// Format Year Numeric - (eg. 2013)
	protected function year ($i)
	{
		$eventTimeZone	= null;
		$year			= JHtml::date($i->next, 'Y', $eventTimeZone);

		return $year;
	}

	// Format Year Short Numeric - (eg. 13)
	protected function yearShort ($i)
	{
		$eventTimeZone	= null;
		$yearShort		= JHtml::date($i->next, 'y', $eventTimeZone);

		return $yearShort;
	}


	////////////
	// DATES
	////////////

	/**
	 * Next Date Text
	 *
	 * @version 3.4.0-rc
	 */
	protected function dateText ($i)
	{
		$eventTimeZone = null;

		$dates		= unserialize($i->dates); // returns array
		$period		= unserialize($i->period); // returns array
		$weekdays	= $i->weekdays;

		$site_today_date	= JHtml::date('now', 'Y-m-d');
		$UTC_today_date		= JHtml::date('now', 'Y-m-d', $eventTimeZone);

		if ($period != NULL)
		{
			$alldates = array_merge($dates, $period);
		}
		else
		{
			$alldates = $dates;
		}

		$next_date		= JHtml::date($i->next, 'Y-m-d', $eventTimeZone);
		$next_datetime	= JHtml::date($i->next, 'Y-m-d H:i', $eventTimeZone);

		if ($period != NULL && in_array($next_datetime, $period))
		{
			$next_is_in_period = true;
		}
		else
		{
			$next_is_in_period = false;
		}

		$totDates = count($alldates);

		if (($next_date > $site_today_date) && ($totDates > 1))
		{
			rsort($alldates);

			$last_date = JHtml::date($alldates[0], 'Y-m-d', $eventTimeZone);

			if ($last_date == $next_date && !$next_is_in_period)
			{
				$dateText = JText::_( 'COM_ICAGENDA_EVENT_DATE_LAST' );
			}
			elseif (!$next_is_in_period)
			{
				$dateText = JText::_( 'COM_ICAGENDA_EVENT_DATE_FUTUR' );
			}
			elseif ($next_is_in_period && $weekdays == NULL)
			{
				$dateText = JText::_( 'COM_ICAGENDA_LEGEND_DATES' );
			}
			else
			{
				$dateText = JText::_( 'COM_ICAGENDA_EVENT_DATE' );
			}
		}
		elseif (($next_date < $site_today_date) && ($totDates > 1))
		{
			if ($totDates == 2)
			{
				$dateText	= $next_is_in_period
							? JText::_( 'COM_ICAGENDA_EVENT_DATE' )
							: JText::_( 'COM_ICAGENDA_EVENT_DATE_PAST' );
			}
			else
			{
				$dateText	= ($next_is_in_period && $weekdays == NULL)
							? JText::_( 'COM_ICAGENDA_LEGEND_DATES' )
							: JText::_( 'COM_ICAGENDA_EVENT_DATE_PAST' );
			}
		}
		elseif (($next_date != $site_today_date) && ($totDates <= 1))
		{
			$dateText = JText::_( 'COM_ICAGENDA_EVENT_DATE' );
		}
		elseif ($next_date == $site_today_date)
		{
			$dateText = $next_is_in_period ? JText::_( 'COM_ICAGENDA_EVENT_DATE_PERIOD_NOW' ) : JText::_( 'COM_ICAGENDA_EVENT_DATE_TODAY' );
		}

		return $dateText;
	}

	/**
	 * Get Next Date (or Last Date)
	 *
	 * @version 3.4.0-rc
	 */
	protected function nextDate ($i)
	{
		$eventTimeZone = null;

		$period			= unserialize($i->period); // returns array
		$startdatetime	= $i->startdate;
		$enddatetime	= $i->enddate;
		$weekdays		= $i->weekdays;

		$site_today_date	= JHtml::date('now', 'Y-m-d');
		$UTC_today_date		= JHtml::date('now', 'Y-m-d', $eventTimeZone);

		$next_date			= JHtml::date($i->next, 'Y-m-d', $eventTimeZone);
		$next_datetime		= JHtml::date($i->next, 'Y-m-d H:i', $eventTimeZone);

		$start_date			= JHtml::date($i->startdate, 'Y-m-d', $eventTimeZone);
		$end_date			= JHtml::date($i->enddate, 'Y-m-d', $eventTimeZone);

		// Check if date from a period with weekdays has end time of the period set in next.
		$time_next_datetime	= JHtml::date($next_datetime, 'H:i', $eventTimeZone);
		$time_startdate		= JHtml::date($i->startdate, 'H:i', $eventTimeZone);
		$time_enddate		= JHtml::date($i->enddate, 'H:i', $eventTimeZone);

		if ($next_date == $site_today_date
			&& $time_next_datetime == $time_enddate)
		{
			$next_datetime = $next_date . ' ' . $time_startdate;
		}

		if ($period != NULL && in_array($next_datetime, $period))
		{
			$next_is_in_period = true;
		}
		else
		{
			$next_is_in_period = false;
		}

		// Highlight event in progress
		if ($next_date == $site_today_date)
		{
			$start_span	= '<span class="ic-next-today">';
			$end_span	= '</span>';
		}
		else
		{
			$start_span = $end_span = '';
		}

		$separator = '<span class="ic-datetime-separator"> - </span>';

		// Format Next Date
		if ( $next_is_in_period
			&& ($start_date == $end_date || $weekdays != null) )
		{
			// Next in the period & (same start/end date OR one or more weekday selected)
			$nextDate = $start_span;
			$nextDate.= '<span class="ic-period-startdate">';
			$nextDate.= $this->formatDate($i->next);
			$nextDate.= '</span>';

			if ($this->displaytime($i) == 1)
			{
				$nextDate.= ' <span class="ic-single-starttime">' . $this->startTime($i) . '</span>';

				if ($this->startTime($i) != $this->endTime($i))
				{
					$nextDate.= $separator . '<span class="ic-single-endtime">' . $this->endTime($i) . '</span>';
				}
			}

			$nextDate.= $end_span;
		}
		elseif ( $next_is_in_period
			&& ($weekdays == null) )
		{
			// Next in the period & different start/end date & no weekday selected
			$start	= '<span class="ic-period-startdate">';
			$start	.= $this->startDate($i);
			$start	.= '</span>';

			$end	= '<span class="ic-period-enddate">';
			$end	.= $this->endDate($i);
			$end	.= '</span>';

			if ($this->displaytime($i) == 1)
			{
				$start		.= ' <span class="ic-period-starttime">' . $this->startTime($i) . '</span>';
				$end		.= ' <span class="ic-period-endtime">' . $this->endTime($i) . '</span>';
			}

			$nextDate = $start_span . $start . $separator . $end . $end_span;
		}
		else
		{
			// Next is a single date
			$nextDate = $start_span;
			$nextDate.= '<span class="ic-single-next">';
			$nextDate.= $this->formatDate($i->next);
			$nextDate.= '</span>';

			if ($this->displaytime($i) == 1)
			{
				$nextDate.= ' <span class="ic-single-starttime">' . $this->evenTime($i) . '</span>';
			}

			$nextDate.= $end_span;
		}

		return $nextDate;
	}


	// Control Upcoming dates Period
	protected function periodControl ($i)
	{
		$eventTimeZone		= null;
		$date_today			= JHtml::date('now', 'Y-m-d');
		$datetime_enddate	= JHtml::date($i->enddate, 'Y-m-d H:i', $eventTimeZone);
		$upPeriod			= '1';

		if (strtotime($datetime_enddate) > strtotime($date_today))
		{
			return $upPeriod;
		}
	}

	// Dates Drop list Registration
	protected function datelistMkt ($i)
	{
		$eventTimeZone		= null;
		$date_today			= JHtml::date('now', 'Y-m-d');
		$allDates			= $this->AllDates($i);
		$timeformat			= $this->options['timeformat'];

		if ($timeformat == 1)
		{
			$lang_time = 'H:i';
		}
		else
		{
			$lang_time = 'h:i A';
		}

		sort($allDates);

		foreach ($allDates as $k => $d)
		{
			$datetime_date = date('Y-m-d H:i:s', strtotime($d));

			if (strtotime($datetime_date) > strtotime($date_today))
			{
				$date = $this->formatDate($d);

				if ($this->displaytime($i) == 1)
				{
					$upDays[$k] = $datetime_date.'@@'.$date.' - '.date($lang_time, strtotime($datetime_date));
				}
				else
				{
					$upDays[$k] = $datetime_date.'@@'.$date;
				}
			}
		}

		if (isset($upDays))
		{
			return $upDays;
		}
	}

	// All Single Dates in Event Details Page
	protected function datelistUl ($i)
	{
		$iCparams		= JComponentHelper::getParams('com_icagenda');
		$timeformat		= $this->options['timeformat'];

		// Hide/Show Option
		$SingleDates			= $iCparams->get('SingleDates', 1);

		// Access Levels Option
//		$accessSingleDates		= $iCparams->get('accessSingleDates', 1);

		// Order by Dates
		$SingleDatesOrder		= $iCparams->get('SingleDatesOrder', 1);

		// List Model
		$SingleDatesListModel	= $iCparams->get('SingleDatesListModel', 1);

		if ($SingleDates == 1)
		{
//			if ($this->accessLevels($accessSingleDates))
//			{
				$days = unserialize($i->dates);

				if ($SingleDatesOrder == 1)
				{
					rsort($days);
				}
				elseif ($SingleDatesOrder == 2)
				{
					sort($days);
				}

				$totDates = count($days);

				if ($timeformat == 1)
				{
					$lang_time = 'H:i';
				}
				else
				{
					$lang_time = 'h:i A';
				}

				// Detect if Singles Dates, and no single date with null value
				$displayDates = false;
				$nbDays = count($days);

				foreach ($days as $k => $d)
				{
					if ($d != '0000-00-00 00:00' && $d != '0000-00-00 00:00:00'
						&& $nbDays != 1)
					{
						$displayDates = true;
					}
				}

				$daysUl = '';

				if ($displayDates)
				{
					if ($SingleDatesListModel == '2')
					{
						$n = 0;
						$daysUl.= '<div class="alldates"><i>'. JText::_( 'COM_ICAGENDA_LEGEND_DATES' ).': </i>';

						foreach ($days as $k => $d)
						{
							$n	= $n+1;
							$fd	= $this->formatDate($d);

							$timeDate	= ($this->displaytime($i) == 1)
										? ' <span class="evttime">'.date($lang_time, strtotime($d)).'</span>'
										: '';

							if ($n <= ($totDates-1))
							{
								$daysUl.= '<span class="alldates">'.$fd.$timeDate.'</span> - ';
							}
							elseif ($n == $totDates)
							{
	   							$daysUl.= '<span class="alldates">'.$fd.$timeDate.'</span>';
							}
						}

						$daysUl.= '</div>';
					}
					else
					{
						$daysUl.= '<ul class="alldates">';

						foreach ($days as $k => $d)
						{
							$fd	= $this->formatDate($d);

							$timeDate	= ($this->displaytime($i) == 1)
										? ' <span class="evttime">'.date($lang_time, strtotime($d)).'</span>'
										: '';

							$daysUl.= '<li class="alldates">'.$fd.$timeDate.'</li>';
						}

						$daysUl.= '</ul>';
					}
				}

				if ($totDates > '0')
				{
					return $daysUl;
				}
				else
				{
					return false;
				}
//			}
//			else
//			{
//				return false;
//			}
		}
		else
		{
			return false;
		}
	}

	// Function Period Display in Registration
	protected function periodDisplay($i)
	{
		if ($this->periodTest($i))
		{
			if (iCDate::isDate($i->startdate) || iCDate::isDate($i->enddate))
			{
				$show = '1';

				return $show;
			}
		}
	}

	// Format Start Date of a period
	protected function startDate($i)
	{
		return $this->formatDate($i->startdate);;
	}

	// Format End Date of a period
	protected function endDate($i)
	{
		return $this->formatDate($i->enddate);
	}

	// Start Day of a period (numeric 1)
	protected function startDay($i)
	{
		$day_format		= 'd-m-Y';
		$start_day		= date($day_format, strtotime($i->startdate));
		$format			= '%e';

		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		{
			$format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
		}

		$startDay	= iCDate::isDate($i->startdate)
					? strftime($format, strtotime($start_day))
					: '&nbsp;&nbsp;';

		return $startDay;
	}

	// End Day of a period (numeric 1)
	protected function endDay($i)
	{
		$day_format		= 'd-m-Y';
		$end_day		= date($day_format, strtotime($i->enddate));
		$format			= '%e';

		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		{
			$format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
		}

		$endDay	= iCDate::isDate($i->enddate)
				? strftime($format, strtotime($end_day))
				: '&nbsp;&nbsp;';

		return $endDay;
	}

	// End Month of a period (numeric 01)
	protected function endMonthNum($i)
	{
		$eventTimeZone	= null;
		$endMonthNum	= JHtml::date($i->enddate, 'm', $eventTimeZone);

		return JText::_($endMonthNum);
	}

	// End Month of a period (text January)
	protected function endMonth($i)
	{
		$eventTimeZone	= null;
		$endMonth		= JHtml::date($i->enddate, 'F', $eventTimeZone);

		return JText::_($endMonth);
	}

	// End Year of a period (numeric 2001)
	protected function endYear($i)
	{
		$eventTimeZone	= null;
		$endYear		= JHtml::date($i->enddate, 'Y', $eventTimeZone);

		return JText::_($endYear);
	}

	// Format Start Time of a period
	protected function startTime($i)
	{
		$eventTimeZone		= null;
		$datetime_startdate	= JHtml::date($i->startdate, 'Y-m-d H:i', $eventTimeZone);
		$timeformat			= $this->options['timeformat'];

		$lang_time = ($timeformat == 1) ? 'H:i' : 'h:i A';

		$startTime = date($lang_time, strtotime($datetime_startdate));

		if ($this->displaytime($i) == 1)
		{
			return $startTime;
		}
	}

	// Format End Time of a period
	protected function endTime($i)
	{
		$eventTimeZone		= null;
		$datetime_enddate	= JHtml::date($i->enddate, 'Y-m-d H:i', $eventTimeZone);
		$timeformat			= $this->options['timeformat'];

		$lang_time = ($timeformat == 1) ? 'H:i' : 'h:i A';

		$endTime = date($lang_time, strtotime($datetime_enddate));

		if ($this->displaytime($i) == 1)
		{
			return $endTime;
		}
	}


	// Display period text width Format Date (eg. from 00-00-0000 to 00-00-0000)
	protected function periodDates ($i)
	{
		$iCparams = JComponentHelper::getParams('com_icagenda');

		// Hide/Show Option
		$PeriodDates = $iCparams->get('PeriodDates', 1);

		// Access Levels Option
		$accessPeriodDates = $iCparams->get('accessPeriodDates', 1);

		// List Model
		$SingleDatesListModel = $iCparams->get('SingleDatesListModel', 1);

		// First day of the week
		$firstday_week_global = $iCparams->get('firstday_week_global', 1);

		// WeekDays
		$weekdays = $i->weekdays;
		$weekdaysall = empty($weekdays) ? true : false;

		if ($firstday_week_global == '1')
		{
			$weekdays_array = explode (',', $weekdays);

			if (in_array('0', $weekdays_array))
			{
				$weekdays = str_replace('0', '', $weekdays);
				$weekdays = $weekdays.',7';
			}
		}

		if (!$weekdaysall)
		{
			$weekdays_array = explode (',', $weekdays);
			$wdaysArray = array();

			foreach ($weekdays_array AS $wd)
			{
				if ($firstday_week_global != '1')
				{
					if ($wd == 0) $wdaysArray[] = JText::_( 'SUNDAY' );
				}
				if ($wd == 1) $wdaysArray[] = JText::_( 'MONDAY' );
				if ($wd == 2) $wdaysArray[] = JText::_( 'TUESDAY' );
				if ($wd == 3) $wdaysArray[] = JText::_( 'WEDNESDAY' );
				if ($wd == 4) $wdaysArray[] = JText::_( 'THURSDAY' );
				if ($wd == 5) $wdaysArray[] = JText::_( 'FRIDAY' );
				if ($wd == 6) $wdaysArray[] = JText::_( 'SATURDAY' );
				if ($firstday_week_global == '1')
				{
					if ($wd == 7) $wdaysArray[] = JText::_( 'SUNDAY' );
				}
			}

			$last  = array_slice($wdaysArray, -1);
			$first = join(', ', array_slice($wdaysArray, 0, -1));
			$both  = array_filter(array_merge(array($first), $last));

			// RTL css if site language is RTL
			$lang = JFactory::getLanguage();

			if ( $lang->isRTL() )
			{
				$arrow_list = '&#8629;';
			}
			else
			{
				$arrow_list = '&#8627;';
			}

			$wdays = $arrow_list . ' <small><i>' . join(' & ', $both) . '</i></small>';
		}
		else
		{
			$wdays = '';
		}

		$showDays ='';

		if ( $PeriodDates == 1 )
		{
			// NOT CURRENTLY USED (is this option needed?)
//			if ( $this->accessLevels($accessPeriodDates) )
//			{
				$startDate	= $this->formatDate($i->startdate);
				$endDate	= $this->formatDate($i->enddate);

				if ($startDate == $endDate)
				{
					$start = $this->startDate($i);
					$end = '';

					if ($this->displaytime($i) == 1)
					{
						if ($this->startTime($i) !== $this->endTime($i))
						{
							$timeOneDay = '<span class="evttime">'.$this->startTime($i).' - '.$this->endTime($i).'</span>';
						}
						else
						{
							$timeOneDay = '<span class="evttime">'.$this->startTime($i).'</span>';
						}
					}
					else
					{
						$timeOneDay = '';
					}
				}
				else
				{
					$start = ucfirst(JText::_( 'COM_ICAGENDA_PERIOD_FROM' )).' '.$this->startDate($i).' <span class="evttime">'.$this->startTime($i).'</span>';
					$end = JText::_( 'COM_ICAGENDA_PERIOD_TO' ).' '.$this->endDate($i).' <span class="evttime">'.$this->endTime($i).'</span>';
					$showDays = $wdays;
					$timeOneDay = '';
				}

				if ($SingleDatesListModel == 2)
				{
					$period = '<div class="alldates"><i>'. JText::_( 'COM_ICAGENDA_EVENT_PERIOD' ).': </i>'.$start.' '.$end.' '.$timeOneDay;
					if (!empty($showDays))
					{
						$period.= '<br /><span style="margin-left:30px">'.$showDays.'</span>';
					}
					$period.= '</div>';
				}
				else
				{
					$period = '<ul class="alldates"><li>'.$start.' '.$end.' '.$timeOneDay;
					if (!empty($showDays))
					{
						$period.= '<br/>'.$showDays;
					}
					$period.= '</li></ul>';
				}

				if ($this->periodTest($i))
				{
					if (($i->startdate!='0000-00-00 00:00:00') AND ($i->enddate!='0000-00-00 00:00:00'))
					{
						return $period;
					}
				}
				else
				{
					return false;
				}
//			}
//			else
//			{
//				return false;
//			}
		}
		else
		{
			return false;
		}
	}


	// Function to get Format Date (using option format, and translation)
	protected function formatDate ($date)
	{
		// Global Option for Date Format
		$date_format_global = JComponentHelper::getParams('com_icagenda')->get('date_format_global', 'Y - m - d');
		$date_format_global = $date_format_global ? $date_format_global : 'Y - m - d';

		// Option for Date Format
		$for = $this->options['format'];

		// default
		if (($for == NULL) || ($for == '0'))
		{
			$for = isset($date_format_global) ? $date_format_global : 'Y - m - d';
		}

		if (!is_numeric($for))
		{
			// update format values, from 2.0.x to 2.1
			if ($for == 'l, d Fnosep Y') {$for = 'l, _ d _ Fnosep _ Y';}
			elseif ($for == 'D d Mnosep Y') {$for = 'D _ d _ Mnosep _ Y';}
			elseif ($for == 'l, Fnosep d, Y') {$for = 'l, _ Fnosep _ d, _ Y';}
			elseif ($for == 'D, Mnosep d, Y') {$for = 'D, _ Mnosep _ d, _ Y';}

			// update format values, from release 2.1.6 and before, to 2.1.7 (using globalization)
			elseif ($for == 'd m Y') {$for = 'd * m * Y';}
			elseif ($for == 'd m y') {$for = 'd * m * y';}
			elseif ($for == 'Y m d') {$for = 'Y * m * d';}
			elseif ($for == 'Y M d') {$for = 'Y * M * d';}
			elseif ($for == 'd F Y') {$for = 'd * F * Y';}
			elseif ($for == 'd M Y') {$for = 'd * M * Y';}
			elseif ($for == 'd msepb') {$for = 'd * m';}
			elseif ($for == 'msepa d') {$for = 'm * d';}
			elseif ($for == 'Fnosep _ d, _ Y') {$for = 'F _ d , _ Y';}
			elseif ($for == 'Mnosep _ d, _ Y') {$for = 'M _ d , _ Y';}
			elseif ($for == 'l, _ d _ Fnosep _ Y') {$for = 'l , _ d _ F _ Y';}
			elseif ($for == 'D _ d _ Mnosep _ Y') {$for = 'D _ d _ M _ Y';}
			elseif ($for == 'l, _ Fnosep _ d, _ Y') {$for = 'l , _ F _ d, _ Y';}
			elseif ($for == 'D, _ Mnosep _ d, _ Y') {$for = 'D , _ M _ d, _ Y';}
			elseif ($for == 'd _ Fnosep') {$for = 'd _ F';}
			elseif ($for == 'Fnosep _ d') {$for = 'F _ d';}
			elseif ($for == 'd _ Mnosep') {$for = 'd _ M';}
			elseif ($for == 'Mnosep _ d') {$for = 'M _ d';}
			elseif ($for == 'Y. F d.') {$for = 'Y . F d .';}
			elseif ($for == 'Y. M. d.') {$for = 'Y . M . d .';}
			elseif ($for == 'Y. F d., l') {$for = 'Y . F d . , l';}
			elseif ($for == 'F d., l') {$for = 'F d . , l';}
		}

		// NEW DATE FORMAT GLOBALIZED 2.1.7

		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$langName = $lang->getName();
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php')){

			$langTag='en-GB';
		}

		$globalize = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php';
		$iso = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/iso.php';

		if (is_numeric($for)) {
			require $globalize;
		} else {
			require $iso;
		}

		// Load Globalization Date Format if selected
		if ($for == '1') {$for = $datevalue_1;}
		elseif ($for == '2') {$for = $datevalue_2;}
		elseif ($for == '3') {$for = $datevalue_3;}
		elseif ($for == '4') {$for = $datevalue_4;}
		elseif ($for == '5') {
			if (($langTag == 'en-GB') OR ($langTag == 'en-US')) {
				$for = $datevalue_5;
			} else {
				$for = $datevalue_4;
			}
		}
		elseif ($for == '6') {$for = $datevalue_6;}
		elseif ($for == '7') {$for = $datevalue_7;}
		elseif ($for == '8') {$for = $datevalue_8;}
		elseif ($for == '9') {
			if ($langTag == 'en-GB') {
				$for = $datevalue_9;
			} else {
				$for = $datevalue_7;
			}
		}
		elseif ($for == '10') {
			if ($langTag == 'en-GB') {
				$for = $datevalue_10;
			} else {
				$for = $datevalue_8;
			}
		}
		elseif ($for == '11') {$for = $datevalue_11;}
		elseif ($for == '12') {$for = $datevalue_12;}

		// Explode components of the date
//		$exformat = explode (' ', $for);
		$for = str_replace(' ', '', $for);
		$for = str_replace('_', ' ', $for);
		$exformat = str_split($for);

		$format='';
		$separator = ' ';

		// Day with no 0 (test if Windows server)
		$dayj = '%e';

		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		{
    		$dayj = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $dayj);
		}

		// Date Formatting using strings of Joomla Core Translations (update 3.1.4)
		$dateFormat = date('d-M-Y', strtotime($date));

		if (isset($this->date_separator)) $separator = $this->date_separator;

		foreach ($exformat as $k => $val)
		{
			switch($val)
			{
				// day (v3)
				case 'd': $val=date("d", strtotime("$dateFormat")); break;
				case 'j': $val=strftime("$dayj", strtotime("$dateFormat")); break;
				case 'D': $val=JText::_(date("D", strtotime("$dateFormat"))); break;
				case 'l': $val=JText::_(date("l", strtotime("$dateFormat"))); break;
				case 'dS': $val=strftime(stristr(PHP_OS,"win") ? "%#d" : "%e", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;
				case 'jS': $val=strftime("$dayj", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;

				// month (v3)
				case 'm': $val=date("m", strtotime("$dateFormat")); break;
				case 'F': $val = JText::_(date('F', strtotime($dateFormat))); break;
				case 'M': $val = JText::_(date('F', strtotime($dateFormat)) . '_SHORT'); break;
				case 'n': $val=date("n", strtotime("$dateFormat")); break;

				// year (v3)
				case 'Y': $val=date("Y", strtotime("$dateFormat")); break;
				case 'y': $val=date("y", strtotime("$dateFormat")); break;

				// separators of the components (v2)
				case '*': $val=$separator; break;
//				case '_': $val=' '; break;
//				case '/': $val='/'; break;
//				case '.': $val='.'; break;
//				case '-': $val='-'; break;
//				case ',': $val=','; break;
//				case 'the': $val='the'; break;
//				case 'gada': $val='gada'; break;
//				case 'de': $val='de'; break;
//				case 'г.': $val='г.'; break;
//				case 'den': $val='den'; break;
//				case 'ukp.': $val = '&#1088;.'; break;


				// day
				case 'N': $val=strftime("%u", strtotime("$dateFormat")); break;
				case 'w': $val=strftime("%w", strtotime("$dateFormat")); break;
				case 'z': $val=strftime("%j", strtotime("$dateFormat")); break;

				// week
				case 'W': $val=date("W", strtotime("$dateFormat")); break;

				// month
				case 'n': $val = $separator . date("n", strtotime("$dateFormat")) . $separator; break;

				// time
				case 'H': $val = date("H", strtotime("$dateFormat")); break;
				case 'i': $val = date("i", strtotime("$dateFormat")); break;

				default: $val; break;
			}

			if ($k!=0)$format.=''.$val;
			if ($k==0)$format.=$val;
		}

		return $format;
	}


	/**
	 * GOOGLE MAPS
	 */

	// Latitude
	protected function lat ($i)
	{
		if (($i->coordinate != NULL) && ($i->lat == '0.0000000000000000'))
		{
			$ex			= explode(', ', $i->coordinate);
			$latresult	= $ex[0];
		}
		elseif ($i->lat != '0.0000000000000000')
		{
			$latresult	= $i->lat;
		}
		else
		{
			$latresult	= NULL;
		}

		return $latresult;
	}

	// Longitude
	protected function lng ($i)
	{
		if (($i->coordinate != NULL) && ($i->lng == '0.0000000000000000'))
		{
			$ex			= explode(', ', $i->coordinate);
			$lngresult	= $ex[1];
		}
		elseif ($i->lng != '0.0000000000000000')
		{
			$lngresult	= $i->lng;
		}
		else
		{
			$lngresult	= NULL;
		}

		return $lngresult;
	}

	// Function Map
	protected function map ($i)
	{
		$maplat	= $this->lat($i);
		$maplng	= $this->lng($i);
		$mapid	= $i->id;

		$iCgmap = '<div class="icagenda_map" id="map_canvas'.(int)$mapid.'" style="width:'.$this->options['m_width'].'; height:'.$this->options['m_height'].'"></div>';
		$iCgmap.= '<script type="text/javascript">';
		$iCgmap.= 'initialize('.$maplat.', '.$maplng.', '.(int)$mapid.');';
		$iCgmap.= '</script>';

		return $iCgmap;
	}

	// Function Map
	protected function coordinate ($i)
	{
		// Hide/Show Option
		$GoogleMaps			= JComponentHelper::getParams('com_icagenda')->get('GoogleMaps', 1);

		// Access Levels Option
		$accessGoogleMaps	= JComponentHelper::getParams('com_icagenda')->get('accessGoogleMaps', 1);

		$maplat				= $this->lat($i);
		$maplng				= $this->lng($i);

		if ($GoogleMaps == 1
			&& $this->accessLevels($accessGoogleMaps)
			&& $maplat != NULL
			&& $maplng != NULL
			)
		{
			return true;
		}

		return false;
	}


	/**
	 * Registered Users List
	 */

	// Participant List Display
	protected function participantList($i)
	{
		$iCparams				= JComponentHelper::getParams('com_icagenda');

		// Get Option if usage of iCagenda registration form for this event
		$evtParams				= $this->evtParams($i);
		$regLink				= $evtParams->get('RegButtonLink', '');

		// Hide/Show Option
		$participantList		= $iCparams->get('participantList', 1);

		// Access Levels Option
		$accessParticipantList	= $iCparams->get('accessParticipantList', 1);

		if ($participantList == 1
			&& !$regLink
			&& $this->accessLevels($accessParticipantList)
			)
		{
			return $participantList;
		}

		return false;
	}


	// Display Title List of Participants (if no slide effect)
	protected function participantListTitle($i)
	{
		// Get Option if usage of iCagenda registration form for this event
		$evtParams			= $this->evtParams($i);
		$regLink			= $evtParams->get('RegButtonLink', '');

		$participantList	= $this->options['participantList'];
		$participantSlide	= $this->options['participantSlide'];

		$registration		= $this->statutReg($i) ? $this->statutReg($i) : '';

		if ($participantSlide == 0
			&& $registration == 1
			&& $participantList == 1
			&& !$regLink
			)
		{
			return JText::_( 'COM_ICAGENDA_EVENT_LIST_OF_PARTICIPANTS');
		}
	}

	// Display Registered Users
	protected function registeredUsers($i)
	{
		$eventTimeZone = null;

		// Get Component PARAMS
		$iCparams = JComponentHelper::getParams('com_icagenda');

		// Preparing connection to db
		$db	= JFactory::getDBO();
		// Preparing the query
		$query = $db->getQuery(true);
		$query->select(' r.userid AS userid, r.name AS registeredUsers, r.date as regDate, r.people as regPeople, r.email as regEmail,
						u.name AS name, u.username AS username')
			->from('#__icagenda_registration AS r')
			->leftJoin('#__users as u ON u.id = r.userid')
			->where('(r.eventId='.(int)$i->id.') AND (r.state > 0)');
		$db->setQuery($query);

		$registeredUsers	= $db->loadObjectList();
		$nbusers			= count($registeredUsers);
		$nbmax				= $nbusers-1;
		$registration		= '';
		$registration		= $this->statutReg($i);
		$n					= '0';

		// Slide Params
		$participantList	= $iCparams->get('participantList', 1);
		$participantSlide	= $iCparams->get('participantSlide', 1);
		$participantDisplay	= $iCparams->get('participantDisplay', 1);
		$fullListColumns	= $iCparams->get('fullListColumns', 'tiers');

		// logged-in Users: Name/User Name Option
		$nameJoomlaUser		= $iCparams->get('nameJoomlaUser', 1);

		// Get Date if set in url as var
		$get_date			= JRequest::getVar('date', null);

		if ($get_date)
		{
			$ex			= explode('-', $get_date);
			$dateday	= $ex['0'].'-'.$ex['1'].'-'.$ex['2'].' '.$ex['3'].':'.$ex['4'];
		}
		else
		{
			$dateday	= '';
		}

		$this_date	= JHtml::date($dateday, 'Y-m-d H:i', $eventTimeZone);

		// Start List of Participants
		jimport( 'joomla.html.html.sliders' );
		$slider_c = '';

		$list_participants = '';

		if ($participantList == 1 && $registration == 1)
		{
			$n_list='names_noslide';

			if ($participantSlide == 1)
			{
				$n_list = 'names_slide';
				$slider_c = 'class="pane-slider content"';
				$list_participants.= JHtml::_('sliders.start', 'icagenda', array('useCookie'=>0, 'startOffset'=>-1, 'startTransition'=>1));
				$list_participants.= JHtml::_('sliders.panel', JText::_('COM_ICAGENDA_EVENT_LIST_OF_PARTICIPANTS'), 'slide1');
			}

			foreach ($registeredUsers as $reguser)
			{
				$this_reg_date	= strtotime($reguser->regDate)
								? JHtml::date($reguser->regDate, 'Y-m-d H:i', $eventTimeZone)
								: $reguser->regDate;

				if ($this_reg_date == $this_date)
				{
					$n = $n+1;
				}
			}

			if ($nbusers == NULL || ($n == 0 && !empty($get_date)))
			{
				$list_participants.= '<div '.$slider_c.'>';
				$list_participants.= '&nbsp;'.JText::_( 'COM_ICAGENDA_NO_REGISTRATION').'&nbsp;';
				$list_participants.= '</div>';
			}
			elseif ($participantDisplay == 1)
			{
				$column = 'tiers';

				if (isset($fullListColumns)) {$column=$fullListColumns;}

				$list_participants.= '<div '.$slider_c.'>';

				foreach ($registeredUsers as $reguser)
				{
					$this_reg_date	= strtotime($reguser->regDate)
									? JHtml::date($reguser->regDate, 'Y-m-d H:i', $eventTimeZone)
									: $reguser->regDate;

					if ($this_reg_date == $this_date || empty($get_date))
					{
						$avatar = md5( strtolower( trim( $reguser->regEmail ) ) );

						// Get Username and name
						if ($reguser->userid && $reguser->userid != 0)
						{
							$data_name		= $reguser->name;
							$data_username	= $reguser->username;

							if ($nameJoomlaUser == 1)
							{
								$reguser->registeredUsers = $reguser->registeredUsers;
							}
							else
							{
								$reguser->registeredUsers = $data_username;
							}
						}

						$regDate = '';

						if (strtotime($reguser->regDate)) // Test if registered date before 3.3.3 could be converted
						{
							// Control if date valid format (Y-m-d H:i)
							$datetime_format	= 'Y-m-d H:i';
							$datetime_input		= $reguser->regDate;
							$datetime_input		= trim($datetime_input);
							$datetime_is_valid	= date($datetime_format, strtotime($datetime_input)) == $datetime_input;

							if ($datetime_is_valid) // New Data value (since 3.3.3)
							{
								$ex_reg_datetime_db	= explode (' ', $datetime_input);
								$registered_date	= $this->formatDate(date('Y-m-d', strtotime($ex_reg_datetime_db['0'])));
								$reg_time_get		= isset($ex_reg_datetime_db['1']) ? $ex_reg_datetime_db['1'] : '';
							}
							else // Test if old date format (before 3.3.3) could be converted. If not, displays old format.
							{
								$ex_reg_datetime	= explode (' - ', trim($reguser->regDate));

								// Control if date valid format (Y-m-d) - Means could be converted
								$date_format		= 'Y-m-d';
								$date_input			= $ex_reg_datetime['0'];
								$date_input			= trim($date_input);
								$date_str			= strtotime($date_input);
								$date_is_valid		= date($date_format, $date_str) == $date_input;

								if ($date_is_valid)
								{
									$registered_date = $this->formatDate(date('Y-m-d', $date_str));
								}
								else
								{
									$registered_date = $ex_reg_datetime['0'];
								}

								$reg_time_get = isset($ex_reg_datetime['1']) ? $ex_reg_datetime['1'] : '';
							}

							$regDate.= $registered_date;

							if ($reg_time_get)
							{
								$regDate.= ' - '.date('H:i', strtotime($reg_time_get));
							}
						}
						else
						{
							$regDate.= $reguser->regDate;
						}

						if ($n <= $nbmax || $n == $nbusers)
						{
							$list_participants.= '<table class="list_table ' . $column . '" cellpadding="0"><tbody><tr><td class="imgbox"><img alt="' . $reguser->registeredUsers . '"  src="http://www.gravatar.com/avatar/' . $avatar . '?s=36&d=mm"/></td><td valign="middle"><span class="list_name">' . $reguser->registeredUsers . '</span><span class="list_places"> (' . $reguser->regPeople . ')</span><br /><span class="list_date">' . $regDate . '</span></td></tr></tbody></table>';
						}
					}
				}
				$list_participants.= '</div>';
			}
			elseif ($participantDisplay == 2)
			{
				$list_participants.= '<div ' . $slider_c . '>';

				foreach ($registeredUsers as $reguser)
				{
					$this_reg_date	= strtotime($reguser->regDate)
									? JHtml::date($reguser->regDate, 'Y-m-d H:i', $eventTimeZone)
									: $reguser->regDate;

					if ($this_reg_date == $this_date || empty($get_date))
					{
						$avatar	= md5(strtolower(trim($reguser->regEmail)));
						$n		= $n+1;


						// Get Username and name
						if (($reguser->userid) AND ($reguser->userid != 0))
						{
							$data_name		= $reguser->name;
							$data_username	= $reguser->username;

							if ($nameJoomlaUser == 1)
							{
								$reguser->registeredUsers = $data_name;
							}
							else
							{
								$reguser->registeredUsers = $data_username;
							}
						}

						if ($n <= $nbmax || $n == $nbusers)
						{
							$list_participants.= '<div style="width: 76px; height: 80px; float:left; margin:2px; text-align:center;"><img style="border-radius: 3px 3px 3px 3px; margin:2px 0px;" alt="' . $reguser->registeredUsers . '"  src="http://www.gravatar.com/avatar/' . $avatar . '?s=48&d=mm"/><br/><strong style="text-align:center; font-size:9px;">' . $reguser->registeredUsers . '</strong></div>';
						}
					}
				}
				$list_participants.= '</div>';
			}
			elseif ($participantDisplay == 3)
			{
				$list_participants.= '<div ' . $slider_c . '>';
				$list_participants.= '<div class="' . $n_list . '">';

				foreach ($registeredUsers as $reguser)
				{
					$this_reg_date	= strtotime($reguser->regDate)
									? JHtml::date($reguser->regDate, 'Y-m-d H:i', $eventTimeZone)
									: $reguser->regDate;

					if ($this_reg_date == $this_date || empty($get_date))
					{
						$n = $n+1;

						// Get Username and name
						if ($reguser->userid && $reguser->userid != 0)
						{
							$data_name		= $reguser->name;
							$data_username	= $reguser->username;

							if ($nameJoomlaUser == 1)
							{
								$reguser->registeredUsers = $data_name;
							}
							else
							{
								$reguser->registeredUsers = $data_username;
							}
						}

						if ($n <= $nbmax)
						{
							$list_participants.= '' . $reguser->registeredUsers . ', ';
						}
						if ($n == $nbusers)
						{
	   						$list_participants.= '' . $reguser->registeredUsers . '';
						}
					}
				}

				$list_participants.= '</div>';
				$list_participants.= '</div>';
			}

			if ($participantSlide == 1)
			{
				$list_participants.= JHtml::_('sliders.end');
			}
		}
		else
		{
			$list_participants.= '';
		}

		return $list_participants;
	}


	/**
	 * SPECIAL FUNCTIONS
	 */

	// function Event Options
	protected function evtParams($i)
	{
		$evtParams = '';
		$evtParams = new JRegistry($i->params);

		return $evtParams;
	}

	// Function if Period Dates exist
	protected function periodTest($i)
	{
		$daysp = unserialize($i->period);

		if ($daysp != NULL)
		{
			return true;
		}

		return false;
	}


	// Function to check if user has access rights to defined function
	protected function accessLevels($accessSet)
	{
		// Get User Access Levels
		$user		= JFactory::getUser();
		$userLevels	= $user->getAuthorisedViewLevels();

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$userGroups = $user->getAuthorisedGroups();
		}
		else
		{
			$userGroups = $user->groups;
		}

		// Control: if access level, or Super User
		if (in_array($accessSet, $userLevels)
			|| in_array('8', $userGroups))
		{
			return true;
		}

		return false;
	}


	// function to detect if info details exist in an event, and to hide or show it depending of Options (display and access levels)
	protected function infoDetails($i)
	{
		// Hide/Show Option
		$infoDetails		= JComponentHelper::getParams('com_icagenda')->get('infoDetails', 1);
		//if (!isset($infoDetails)) $infoDetails='1';

		// Access Levels Option
		$accessInfoDetails	= JComponentHelper::getParams('com_icagenda')->get('accessInfoDetails', 1);

		if ($infoDetails == 1
			&& $this->accessLevels($accessInfoDetails))
		{
			if ( !$this->placeLeft($i)
				&& !$i->place_name
				&& !$i->phone
				&& !$i->email
				&& !$i->website
				&& !$i->address
				&& !$i->file
//				&& !$this->loadEventCustomFields($i)
				)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * ADDTHIS - Social Networks
	 */

	// function to override general options display of AddThis in event details view
	protected function ateventshow($i)
	{
		$atevent		= $this->options['atevent'];
		$evtParams		= $this->evtParams($i);
		$eventatvent	= $evtParams->get('atevent', '');

		$show = ($eventatvent == '') ? $atevent : $eventatvent;

		return $show;
	}

	// function option display AddThis social networks sharing
	protected function share_event($i)
	{
		$at = $this->ateventshow($i);

		if ($at == 1)
		{
			$share = $this->share($i);
		}
		else
		{
			$share = NULL;
		}

		return $share;
	}

	// function AddThis social networks sharing
	protected function share ($i)
	{
		$addthis	= $this->options['addthis'];
		$float		= $this->options['atfloat'];
		$icon		= $this->options['aticon'];

		if ($float == 1)
		{
			$floataddthis	= 'floating';
			$float_position	= 'position: fixed;';
			$float_side		= 'left';
		}
		elseif ($float == 2)
		{
			$floataddthis	= 'floating';
			$float_position	= 'position: fixed;';
			$float_side		= 'right';
		}
		else
		{
			$floataddthis	= 'default';
			$float_position	= '';
			$float_side		= 'right';
		}

		if ($icon == 2)
		{
			$iconaddthis	= '32x32';
		}
		else
		{
			$iconaddthis	= '16x16';
		}

		$at_div = '<div class="share ic-share" style="' . $float_position . '">';
		$at_div.= '<!-- AddThis Button BEGIN -->';
		$at_div.= '<div class="addthis_toolbox';
		$at_div.= ' addthis_' . $floataddthis . '_style';
		$at_div.= ' addthis_' . $iconaddthis . '_style"';
		$at_div.= ' style="' . $float_side . ': 2%; top: 40%;">';
		$at_div.= '<a class="addthis_button_preferred_1"></a>';
		$at_div.= '<a class="addthis_button_preferred_2"></a>';
		$at_div.= '<a class="addthis_button_preferred_3"></a>';
		$at_div.= '<a class="addthis_button_preferred_4"></a>';
		$at_div.= '<a class="addthis_button_compact"></a>';
		$at_div.= '<a class="addthis_counter addthis_bubble_style"></a>';
		$at_div.= '</div>';

		if ($addthis)
		{
			$at_div.= '<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>';
			$at_div.= '<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=' . $this->options['addthis'] . '"></script>';
		}
		else
		{
			$at_div.= '<script type="text/javascript">var addthis_config = {"data_track_addressbar":false};</script>';
			$at_div.= '<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5024db5322322e8b"></script>';
		}

		$at_div.= '<!-- AddThis Button END -->';
		$at_div.= '</div>';

		return $at_div;
	}


	/**
	 * REGISTRATIONS
	 */

	// function url to iCagenda registration page
	protected function iCagendaRegForm ($i)
	{
		$event_slug = empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;

		$iCagendaRegForm = JROUTE::_('index.php?option=com_icagenda&view=list&layout=registration&Itemid='. (int) $this->options['Itemid'] . '&id=' . $event_slug);

		return $iCagendaRegForm;
	}

	// function link to registration page
	protected function regUrl($i)
	{
		$event_slug = empty($i->alias) ? $i->id : $i->id . ':' . $i->alias;

		$icagenda_form = JROUTE::_('index.php?option=com_icagenda&view=list&layout=registration&Itemid='. (int) $this->options['Itemid'] . '&id=' . $event_slug);

		$evtParams			= $this->evtParams($i);
		$regLink			= $evtParams->get('RegButtonLink', '');
		$regLinkArticle		= $evtParams->get('RegButtonLink_Article', $icagenda_form);
		$regLinkUrl			= $evtParams->get('RegButtonLink_Url', $icagenda_form);
		$RegButtonTarget	= $evtParams->get('RegButtonTarget', '0');

		if ($RegButtonTarget == 1)
		{
			$browserTarget = '_blank';
		}
		else
		{
			$browserTarget = '_parent';
		}

		if ($regLink == 1 && is_numeric($regLinkArticle))
		{
			$regUrl = JURI::root() . 'index.php?option=com_content&view=article&id=' . $regLinkArticle . '" rel="nofollow" target="' . $browserTarget;
		}
		elseif ($regLink == 2)
		{
			$regUrl = $regLinkUrl . '" rel="nofollow" target="' . $browserTarget;
		}
		else
		{
			$regUrl = $icagenda_form . '" rel="nofollow" target="' . $browserTarget;
		}

		return $regUrl;
	}

	// function Registration statut
	protected function statutReg($i)
	{
		$gstatutReg		= $this->options['statutReg'];

		$evtParams		= $this->evtParams($i);
		$evtstatutReg	= $evtParams->get('statutReg', '');

		// Control and edit param values to iCagenda v3
		if ($evtstatutReg == '2')
		{
			$evtstatutReg = '0';
		}

		$statutReg = ($evtstatutReg != '') ? $evtstatutReg : $gstatutReg;

		return $statutReg;
	}

	// function Registration Access
	protected function accessReg($i)
	{
		$reg_form_access	= JComponentHelper::getParams('com_icagenda')->get('reg_form_access', 1);
		$evtParams			= $this->evtParams($i);
		$accessReg			= $evtParams->get('accessReg', $reg_form_access);

		return $accessReg;
	}

	// function Registration Type
	protected function typeReg($i)
	{
		$evtParams	= $this->evtParams($i);
		$typeReg	= $evtParams->get('typeReg', '');

		return $typeReg;
	}

	// function Max places per registration
	protected function maxRlist($i)
	{
		$maxRlist = '';
		$gmaxRlist = $this->options['maxRlist'];

		$evtParams			= $this->evtParams($i);
		$evtmaxRlistGlobal	= $evtParams->get('maxRlistGlobal');
		$evtmaxRlist		= $evtParams->get('maxRlist');

		// Control and edit param values to iCagenda v3
		if ($evtmaxRlistGlobal == '1')
		{
			$evtmaxRlistGlobal = '';
		}
		elseif ($evtmaxRlistGlobal == '0')
		{
			$evtmaxRlistGlobal = '2';
		}

		if ($evtmaxRlistGlobal == '2')
		{
			$maxRlist = $evtmaxRlist;
		}
		else
		{
			$maxRlist = $gmaxRlist;
		}

		return $maxRlist;
	}

	// Keep for B/C : DEPRECATED!
	// Function Max Registrations per event (OLD before 3.2.8, for use with old theme packs or custom one)
	protected function maxReg($i)
	{
		$evtParams	= $this->evtParams($i);
		$maxReg		= $evtParams->get('maxReg', '1000000');

		return $maxReg;
	}

	// function Max Nb Tickets (Control if set)
	protected function maxNbTickets($i)
	{
		$maxNbTickets = '1000000';

		$evtParams = $this->evtParams($i);
		$maxNbTickets = $evtParams->get('maxReg', '1000000');

		if ($maxNbTickets != '1000000'
			&& ($this->statutReg($i) == '1'))
		{
			return $maxNbTickets;
		}
	}

	// function Number of places left
	protected function placeLeft($i)
	{
		$maxReg			= $this->maxReg($i);
		$registered		= $this->registered($i);

		if ($maxReg != '1000000'
			&& ($this->statutReg($i) == '1'))
		{
			return ($maxReg - $registered);
		}
	}

	// function Email Required
	protected function emailRequired($i)
	{
		return $this->options['emailRequired'];
	}

	// function Phone Required
	protected function phoneRequired($i)
	{
		return $this->options['phoneRequired'];
	}

	// function pre-formated to display Register button and registered bubble
	protected function reg($i)
	{
		$reg		= $this->statutReg($i);
		$accessreg	= $this->accessReg($i);
		$nbreg		= $this->registered($i);
		$maxreg		= $this->maxReg($i);
		$pastDates	= $this->pastDates($i);

		// Initialize controls
		$access		= '0';
		$control	= '';
		$TextRegBt	= '';

		// Access Control
		$user		= JFactory::getUser();
		$userLevels	= $user->getAuthorisedViewLevels();

		$evtParams	= $this->evtParams($i);
		$regLink	= $evtParams->get('RegButtonLink', '');

		if ($evtParams->get('RegButtonText'))
		{
			$TextRegBt = $evtParams->get('RegButtonText');
		}
		elseif ($this->options['RegButtonText'])
		{
			$TextRegBt = $this->options['RegButtonText'];
		}
		else
		{
			$TextRegBt = JText::_( 'COM_ICAGENDA_REGISTRATION_REGISTER');
		}

		$regButton_type = ''; // DEV.

		if ($regButton_type == 'button') // DEV.
		{
			$doc = JFactory::getDocument();
			$style = '.regis_button {'
					. 'text-transform: none !important;'
					. 'padding: 10px 14px 10px;'
					. '-webkit-border-radius: 10px;'
					. '-moz-border-radius: 10px;'
					. 'border-radius: 10px;'
					. 'color: #FFFFFF;'
					. 'background-color: #D90000;'
					. '*background-color: #751616;'
					. 'background-image: -ms-linear-gradient(top,#D90000,#751616);'
					. 'background-image: -webkit-gradient(linear,0 0,0 100%,from(#D90000),to(#751616));'
					. 'background-image: -webkit-linear-gradient(top,#D90000,#751616);'
					. 'background-image: -o-linear-gradient(top,#D90000,#751616);'
					. 'background-image: linear-gradient(top,#D90000,#751616);'
					. 'background-image: -moz-linear-gradient(top,#D90000,#751616);'
					. 'background-repeat: repeat-x;'
					. 'filter: progid:dximagetransform.microsoft.gradient(startColorstr="#D90000",endColorstr="#751616",GradientType=0);'
					. 'filter: progid:dximagetransform.microsoft.gradient(enabled=false);'
					. '*zoom: 1;'
					. '-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);'
					. '-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);'
					. 'box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);'
					. '}'
					. '.regis_button:hover {'
					. 'color: #F9F9F9;'
					. 'background-color: #b60000;'
					. '*background-color: #531111;'
					. 'background-image: -ms-linear-gradient(top,#b60000,#531111);'
					. 'background-image: -webkit-gradient(linear,0 0,0 100%,from(#b60000),to(#531111));'
					. 'background-image: -webkit-linear-gradient(top,#b60000,#531111);'
					. 'background-image: -o-linear-gradient(top,#b60000,#531111);'
					. 'background-image: linear-gradient(top,#b60000,#531111);'
					. 'background-image: -moz-linear-gradient(top,#b60000,#531111);'
					. 'background-repeat: repeat-x;'
					. 'filter: progid:dximagetransform.microsoft.gradient(startColorstr="#b60000",endColorstr="#531111",GradientType=0);'
					. 'filter: progid:dximagetransform.microsoft.gradient(enabled=false);'
					. '*zoom: 1;'
					. '}';
			$doc->addStyleDeclaration( $style );
		}


		if ($reg == 1)
		{
			$reg_button = '<div class="ic-registration-box">';

			if ($pastDates == 1
				&& $nbreg < $maxreg
//				&& in_array($accessreg, $userLevels)
				)
			{
				$reg_button.= '<a href="' . $this->regUrl($i) . '">';

				if (in_array($accessreg, $userLevels))
				{
					$reg_button.= '<div class="ic-btn ic-btn-success ic-btn-small ic-event-register regis_button">';
					$reg_button.= '<i class="iCicon iCicon-register"></i>&nbsp;' . $TextRegBt;
					$reg_button.= '</div>';
				}
				else
				{
					$reg_button.= '<div class="ic-btn ic-btn-danger ic-btn-small ic-event-register regis_button">';
					$reg_button.= '<i class="iCicon iCicon-private"></i>&nbsp;' . $TextRegBt;
					$reg_button.= '</div>';
				}

				$reg_button.= '</a>';
			}

//				$accessInfo = 'alert( \'' . JText::_( 'JERROR_ALERTNOAUTHOR' ) . ' \n\n ' . JText::_( 'JGLOBAL_YOU_MUST_LOGIN_FIRST' ) . '\' )';

				// Redirect to login page if no access to registration form
//				$uri = JFactory::getURI();
//				$urlregistration = $this->iCagendaRegForm($i);

//				$return     = base64_encode($urlregistration.'?tmpl=component');
//				$return     = base64_encode($urlregistration);
//				$rlink = "index.php?option=com_users&view=login&tmpl=component&return=$return";
//				$rlink = "index.php?option=com_users&view=login&return=$return";
//				$msg = JText::_("COM_ICAGENDA_LOGIN_TO_ACCESS_REGISTRATION_FORM");


//				$reg_button.= '<script type="text/javascript">';
//				$reg_button.= '		window.setTimeout(\'closeme();\', 300);';
//				$reg_button.= '		function closeme()';
//				$reg_button.= '		{';
//				$reg_button.= '			parent.SqueezeBox.close();';
//				$reg_button.= '		}';
//				$reg_button.= '	</script>';
//				$reg_button.= '<a href="'.$rlink.'" class="modal" rel="{size: {x: 500, y: 400}, handler:\'iframe\'}">';

			elseif ($pastDates == 1
				&& $nbreg >= $maxreg
				)
			{
				$reg_button.= '<div class="ic-btn ic-btn-info ic-btn-small ic-event-full">';
				$reg_button.= '<i class="iCicon iCicon-people"></i>&nbsp;' . JText::_( 'COM_ICAGENDA_REGISTRATION_EVENT_FULL');
				$reg_button.= '</div>';
			}
			elseif ($pastDates == 0)
			{
				$reg_button.= '<div class="ic-btn ic-btn-default ic-btn-small ic-event-finished">';
				$reg_button.= '<i class="iCicon iCicon-blocked"></i>&nbsp;' . JText::_( 'COM_ICAGENDA_REGISTRATION_EVENT_FINISHED');
				$reg_button.= '</div>';
			}
			else
			{
				return false;
			}

			if (!$regLink)
			{
				$reg_button.= '&nbsp;<i class="iCicon iCicon-people ic-people"></i>';
				$reg_button.= '<div class="ic-registered" >' . $this->registered($i) . '</div>';
			}

			$reg_button.= '</div>';
		}
		else
		{
			return false;
		}

		return $reg_button;
	}


	// function to get number of registered people to an event
	protected function registered($i)
	{
		$reg	= $this->statutReg($i);
		$nbreg	= $i->registered;

		if ($reg == 1 && $nbreg == NULL)
		{
			$nbreg = '0';
		}

		return $nbreg;
	}

	// url to return event details after registration (changed in 2.1.14 not in use; see $urlist)
	protected function urlList($i)
	{
		$db	= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(' r.eventId AS idevt')->from('#__icagenda_registration AS r');
		$db->setQuery($query);
		$idevt = $db->loadObjectList();

		$link		= $this->options['Itemid'];
		$urllist	= JROUTE::_('index.php?option=com_icagenda&view=list&layout=event&id='.(int)$idevt.'&Itemid='.(int)$link);
		$url		= $urllist;

		if (is_numeric($link) && !is_array($link))
		{
			return $url;
		}
		else
		{
			$url = JROUTE::_('index.php');

			return $url;
		}
	}

	/**
	 * Loads the Event's custom fields for this item
	 *
	 * @return object list.
	 * @since   3.4.0
	 */
	public function loadEventCustomFields($i)
	{
		// Get the database connector.
		$db = JFactory::getDBO();

		// Get the query from the database connector.
		$query = $db->getQuery(true);

		// Build the query programatically (using chaining if desired).
		$query->select('cfd.*, cf.title AS title')
			// Use the qn alias for the quoteName method to quote table names.
			->from($db->qn('#__icagenda_customfields_data') . ' AS cfd');

		$query->leftJoin('#__icagenda_customfields AS cf ON cf.slug = cfd.slug');

		$query->where($db->qn('cfd.parent_id').' = '.(int) $i->id);
		$query->where($db->qn('cfd.parent_form').' = 2');
		$query->where($db->qn('cf.parent_form').' = 2');
		$query->where($db->qn('cfd.state').' = 1');
		$query->where($db->qn('cf.state').' = 1');
		$query->order('cf.ordering ASC');

		// Tell the database connector what query to run.
		$db->setQuery($query);

		// Invoke the query or data retrieval helper.
		return $db->loadObjectList();
	}


	// Save of a registration, and automatic email (TO BE MOVED TO A NEW MODEL/VIEW)
	public function registration($array)
	{
		$menu_items	= icagendaMenus::iClistMenuItems();
		$itemid		= JRequest::getVar('Itemid');

		foreach ($menu_items as $l)
		{
			if (($l->published == '1') && ($l->id == $itemid))
			{
				$linkexist = '1';
			}
		}

		if (is_numeric($itemid)
			&& $itemid != 0
			&& $linkexist == 1
			)
		{
			// get the application object
			$app			= JFactory::getApplication();
			$isSef			= $app->getCfg( 'sef' );
			$eventTimeZone	= null;

			$data = new stdClass();

			$data->id = null;
			$data->eventid = '0';

			if(isset($array['uid'])) $data->userid = $array['uid'];
			if(isset($array['name'])) $data->name = $array['name'];
			if(isset($array['email'])) $data->email = $array['email'];
			if(isset($array['phone'])) $data->phone = $array['phone'];
			if(isset($array['date'])) $data->date = $array['date'];
			if(isset($array['period'])) $data->period = $array['period'];
			if(isset($array['people'])) $data->people = $array['people'];
			if(isset($array['notes'])) $data->notes = htmlentities(strip_tags($array['notes']));
			if(isset($array['event'])) $data->eventid = $array['event'];
			if(isset($array['menuID'])) $data->itemid = $array['menuID'];

			$current_url		= isset($array['current_url']) ? $array['current_url'] : 'index.php';
			$max_nb_of_tickets	= isset($array['max_nb_of_tickets']) ? $array['max_nb_of_tickets'] : '1000000';

			// Set Form Data to Session
			$session = JFactory::getSession();
			$session->set('ic_registration', $array);
			$session->set('custom_fields', $array['custom_fields']);
			$session->set('ic_submit_tos', $array['submit_tos']);

			// Control if still ticket left
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			// Registrations total
			$query->select('sum(r.people) AS registered');
			$query->from('`#__icagenda_registration` AS r');
			$query->where('r.state > 0');
			$query->where('r.eventid = ' . $data->eventid);
			$db->setQuery($query);
			$registered = $db->loadObject()->registered;

			$date_in_url = $data->date ? iCDate::dateToAlias($data->date) : false;

			$data->checked_out_time = date('Y-m-d H:i:s');

			// Get the "event" URL
			$baseURL = JURI::base();
			$subpathURL = JURI::base(true);

			// To be tested
//			$temp = str_replace('http://', '', $baseURL);
//			$temp = str_replace('https://', '', $temp);
//			$parts = explode($temp, '/', 2);
//			$subpathURL = count($parts) > 1 ? $parts[1] : '';

			$baseURL = str_replace('/administrator', '', $baseURL);
			$subpathURL = str_replace('/administrator', '', $subpathURL);

			if ($isSef == '1')
			{
				$this_date = $date_in_url ? '?date='.$date_in_url : '';
			}
			else
			{
				$this_date = $date_in_url ? '&date='.$date_in_url : '';
			}

			$urlevent = str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&view=list&layout=event&Itemid='.(int)$data->itemid.'&id='.(int)$data->eventid).$this_date);
			$urllist = str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&view=list&Itemid='.(int)$data->itemid));
			$urlregistration = str_replace('&amp;','&', JRoute::_('index.php?option=com_icagenda&view=list&layout=registration&Itemid='.(int)$data->itemid.'&id='.(int)$data->eventid));
//			}

			// Sub Path filtering
			$subpathURL = ltrim($subpathURL, '/');

			// URL Event Details filtering
			$urlevent = ltrim($urlevent, '/');
			if (substr($urlevent, 0, strlen($subpathURL) + 1) == "$subpathURL/") $urlevent = substr($urlevent, strlen($subpathURL) + 1);
			$urlevent = rtrim($baseURL, '/') . '/' . ltrim($urlevent, '/');

			// URL List filtering
			$urllist = ltrim($urllist, '/');
			if(substr($urllist,0,strlen($subpathURL)+1) == "$subpathURL/") $urllist = substr($urllist,strlen($subpathURL)+1);
			$urllist = rtrim($baseURL,'/').'/'.ltrim($urllist,'/');

			// URL Registration filtering
			$urlregistration = ltrim($urlregistration, '/');
			if(substr($urlregistration,0,strlen($subpathURL)+1) == "$subpathURL/") $urlregistration = substr($urlregistration,strlen($subpathURL)+1);
			$urlregistration = rtrim($baseURL,'/').'/'.ltrim($urlregistration,'/');

			$name_isValid = '1';

			$pattern = "#[/\\\\/\<>/\"%;=\[\]\+()&]|^[0-9]#i";

        	if (isset($array['name']))
        	{
				$nbMatches = preg_match($pattern, $array['name']);

				if ($nbMatches && $nbMatches==1)
				{
					// message if invalid characters
					$app->redirect(htmlspecialchars_decode($urlregistration), JText::sprintf( 'COM_ICAGENDA_REGISTRATION_NAME_NOT_VALID' , '<b>'.htmlentities($array['name'], ENT_COMPAT, 'UTF-8').'</b>'), JText::_( 'JGLOBAL_VALIDATION_FORM_FAILED' ));
					$name_isValid = '0';

					return false;
				}

				if (strlen(utf8_decode($array['name']))<2)
				{
					// message if less than 2 characters in the name
					$app->redirect(htmlspecialchars_decode($urlregistration), JText::_( 'COM_ICAGENDA_REGISTRATION_NAME_MINIMUM_CHARACTERS'), JText::_( 'JGLOBAL_VALIDATION_FORM_FAILED' ));
					$name_isValid = '0';

					return false;
				}
        	}

			$data->name = filter_var($data->name, FILTER_SANITIZE_STRING);

			$emailCheckdnsrr='0';
			$emailCheckdnsrr = JComponentHelper::getParams('com_icagenda')->get('emailCheckdnsrr');

			if (!empty($data->email))
			{
				$validEmail = true;
				$checkdnsrr = true;
				if (($emailCheckdnsrr == 1) AND (function_exists('checkdnsrr')))
				{
					$provider = explode('@', $data->email);
					if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					{
						if (version_compare(phpversion(), '5.3.0', '<'))
						{
							$checkdnsrr = true;
						}
					}
					else
					{
						$checkdnsrr = checkdnsrr($provider[1]);
					}
				}
				else
				{
					$checkdnsrr = true;
				}
			}
			else
			{
				$checkdnsrr = true;
			}

			if($validEmail) $validEmail = $this->validEmail($data->email);

			if (((($validEmail) AND ($checkdnsrr)) OR ($data->email==NULL)) AND ($name_isValid == '1'))
			{
				$eventid = $data->eventid;

				if ($period != NULL) {$period = $data->period;} else {$period = '0';}

				$people = $data->people;
				$name = $data->name;
				$email = $data->email;
				$phone = $data->phone;
				$notes = html_entity_decode($data->notes);
				$dateReg = $data->date;

				// Import params - Limit Options for User Registration
				$app = JFactory::getApplication();
				$icpar = $app->getParams();

				$limitRegEmail = $icpar->get('limitRegEmail', 1);
				$limitRegDate = $icpar->get('limitRegDate', 1);
				$emailRequired = $icpar->get('emailRequired', 1);

				$alreadyexist='no';

				if (($limitRegEmail == 1) OR ($limitRegDate == 1))
				{
					$cf = JRequest::getString('email', '', 'post');

					if ($limitRegDate == 0)
					{
						$query = "
							SELECT COUNT(*)
							FROM `#__icagenda_registration`
							WHERE `email` = '$cf' AND `eventid`='$eventid' AND `state`='1'
						";
					}
					elseif ($limitRegDate == 1)
					{
						$query = "
							SELECT COUNT(*)
							FROM `#__icagenda_registration`
							WHERE `email` = '$cf' AND `eventid`='$eventid' AND `date`='$dateReg' AND `state`='1'
						";
					}

					$db->setQuery($query);

//					if (($emailRequired != '0') AND ($email!=NULL)) {
					if ($email != NULL)
					{
						if ( $db->loadResult() )
						{
							$alreadyexist='yes';
							JError::raiseWarning( 100, JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_ALERT' ).' '.$email.'<br>' );

							//get the application object
							$app = JFactory::getApplication();
							//redirect to the event page
							$app->redirect(htmlspecialchars_decode($urlregistration));
							return false;
						}
						else
						{
							$alreadyexist='no';
						}
					}
				}

				if ($email == '')
				{
					$email = JText::_( 'COM_ICAGENDA_NOT_SPECIFIED' );
				}

				if ($phone == '')
				{
					$phone = JText::_( 'COM_ICAGENDA_NOT_SPECIFIED' );
				}

				// RECAPTCHA
				$reg_captcha = JComponentHelper::getParams('com_icagenda')->get('reg_captcha', 1);

				if ($reg_captcha != '0')
				{
					JPluginHelper::importPlugin('captcha');

					// JOOMLA 3.x/2.5 SWITCH
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						$dispatcher = JEventDispatcher::getInstance();
					}
					else
					{
						$dispatcher = JDispatcher::getInstance();
					}

					$res = $dispatcher->trigger('onCheckAnswer', $array['recaptcha_response_field']);

					if (!$res[0])
					{
						// message if captcha is invalid
						$app->enqueueMessage(JText::_( 'PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL' ), 'error');
						$app->redirect(htmlspecialchars_decode($current_url));

						return false;
					}
				}

				// clear the data so we don't process it again
				$session->clear('ic_registration');
				$session->clear('custom_fields');
				$session->clear('ic_submit_tos');

				// Check number of tickets left
				$tickets_available = $max_nb_of_tickets - $registered;

				if ($tickets_available <= 0)
				{
					$app->enqueueMessage(JText::_('COM_ICAGENDA_ALERT_NO_TICKET_AVAILABLE'), 'warning');

					$app->redirect(htmlspecialchars_decode($urlevent));
				}
				elseif ($tickets_available < $data->people)
				{
					$msg = JText::_('COM_ICAGENDA_ALERT_NOT_ENOUGH_TICKETS_AVAILABLE') . '<br />';
					$msg.= JText::sprintf('COM_ICAGENDA_ALERT_NOT_ENOUGH_TICKETS_AVAILABLE_NOW', $tickets_available) . '<br />';
					$msg.= JText::_('COM_ICAGENDA_ALERT_NOT_ENOUGH_TICKETS_AVAILABLE_CHANGE_NUMBER');

					$app->enqueueMessage($msg, 'warning');

					$app->redirect(htmlspecialchars_decode($urlevent));
				}

				// Insert data of the registered user (Option Email required)
				if ($emailRequired == '1')
				{
					if (is_numeric($eventid) && is_numeric($period) && is_numeric($people) && ($name != NULL) && ($email != NULL))
					{
						$db->insertObject( '#__icagenda_registration', $data, id );
					}
				}
				else
				{
					if (is_numeric($eventid) && is_numeric($period) && is_numeric($people) && ($name != NULL))
					{
						$db->insertObject( '#__icagenda_registration', $data, id );
					}
				}


				/**
				 *	CUSTOM FIELDS TO DATA
				 */
				$custom_fields = isset($array['custom_fields']) ? $array['custom_fields'] : false;

				// Save Custom Fields to database
				if ($custom_fields && is_array($custom_fields))
				{
					icagendaCustomfields::saveToData($custom_fields, $data->id, 1);
				}


				/**
				 *	NOTIFICATION EMAILS
				 */
				$author= '0';

				// Preparing the query
				$query = $db->getQuery(true);
				$query->select('e.title AS title, e.startdate AS startdate, e.enddate AS enddate,
						e.created_by AS authorID, e.email AS contactemail, e.displaytime AS displaytime')
					->from('#__icagenda_events AS e')
					->where("(e.id=$data->eventid)");
				$db->setQuery($query);
				$title			= $db->loadObject()->title;
				$startdate		= $db->loadObject()->startdate;
				$enddate		= $db->loadObject()->enddate;
				$authorID		= $db->loadObject()->authorID;
				$contactemail	= $db->loadObject()->contactemail;
				$displayTime	= $db->loadObject()->displaytime;

				$startD = $this->formatDate($startdate);
				$endD = $this->formatDate($enddate);
//				$startT = date('H:i', $startdate);
//				$endT = date('H:i', $enddate);
				$startT = JHtml::date($startdate, 'H:i', $eventTimeZone);
				$endT = JHtml::date($enddate, 'H:i', $eventTimeZone);

				$regDate = $this->formatDate($data->date);
//				$regTime = date('H:i', $data->date);
				$regTime = JHtml::date($data->date, 'H:i', $eventTimeZone);

				$regDateTime		= !empty($displayTime) ? $regDate.' - '.$regTime : $regDate;
				$regStartDateTime	= !empty($displayTime) ? $startD.' - '.$startT : $startD;
				$regEndDateTime		= !empty($displayTime) ? $endD.' - '.$endT : $endD;

				$periodreg = $data->period;

				// Import params
				$app = JFactory::getApplication();
				$icpar = $app->getParams();

				$defaultemail			= $icpar->get('regEmailUser', '1');
				$emailUserSubjectPeriod	= $icpar->get('emailUserSubjectPeriod', '');
				$emailUserBodyPeriod	= $icpar->get('emailUserBodyPeriod', '');
				$emailUserSubjectDate	= $icpar->get('emailUserSubjectDate', '');
				$emailUserBodyDate		= $icpar->get('emailUserBodyDate', '');

				$emailAdminSend			= $icpar->get('emailAdminSend', '1');
				$emailAdminSend_select	= $icpar->get('emailAdminSend_select', array('0'));
				$emailAdminSend_custom	= $icpar->get('emailAdminSend_Placeholder', '');

				$emailUserSend			= $icpar->get('emailUserSend', '1');

				$eUSP = isset($emailUserSubjectPeriod)
						? $emailUserSubjectPeriod
						: JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_PERIOD_DEFAULT_SUBJECT' );

				$eUBP = isset($emailUserBodyPeriod)
						? $emailUserBodyPeriod
						: JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_PERIOD_DEFAULT_BODY' );

				$eUSD = isset($emailUserSubjectDate)
						? $emailUserSubjectDate
						: JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_DATE_DEFAULT_SUBJECT' );

				$eUBD = isset($emailUserBodyDate)
						? $emailUserBodyDate
						: JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_DATE_DEFAULT_BODY' );

				$period_set = substr($startdate, 0, 4);

				if ($periodreg == 1)
				{
					$periodd = ($period_set != '0000')
								? JText::sprintf( 'COM_ICAGENDA_REGISTERED_EVENT_PERIOD', $startD, $startT, $endD, $endT )
								: '';
					$adminsubject = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_ADMIN_DEFAULT_SUBJECT' );
					$adminbody = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_ADMIN_PERIOD_DEFAULT_BODY' );

					if ($defaultemail == 0)
					{
						$subject = $eUSP;
						$body = $eUBP;
					}
					else
					{
						$subject = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_PERIOD_DEFAULT_SUBJECT' );
						$body = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_PERIOD_DEFAULT_BODY' );
					}

				}
				else
				{
					$periodd = ($period_set != '0000')
								? JText::sprintf( 'COM_ICAGENDA_REGISTERED_EVENT_DATE', $regDate, '' )
								: '';
					$adminsubject = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_ADMIN_DEFAULT_SUBJECT' );
					$adminbody = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_ADMIN_DATE_DEFAULT_BODY' );

					if ($defaultemail == 0)
					{
						$subject = $eUSD;
						$body = $eUBD;
					}
					else
					{
						$subject = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_DATE_DEFAULT_SUBJECT' );
						$body = JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_USER_DATE_DEFAULT_BODY' );
					}
				}

				// Get the site name
				$config = JFactory::getConfig();

				// Joomla 3.x / 2.5 SWITCH
				if(version_compare(JVERSION, '3.0', 'ge'))
				{
					$sitename = $config->get('sitename');
				}
				else
				{
					$sitename = $config->getValue('config.sitename');
				}

				$siteURL = JURI::base();
				$siteURL = rtrim($siteURL,'/');

				// Get Author Email
				$authormail = '';

				if ($authorID != NULL)
				{
					// Preparing the query
					$query = $db->getQuery(true);
					$query->select('email AS authormail, name AS authorname')->from('#__users AS u')->where("(u.id=$authorID)");
					$db->setQuery($query);
					$authormail = $db->loadObject()->authormail;
					$authorname = $db->loadObject()->authorname;

					if ($authormail == NULL)
					{
//						if (version_compare(JVERSION, '3.0', 'ge'))
//						{
//							$authormail = $config->get('mailfrom');
//						}
//						else
//						{
//							$authormail = $config->getValue('config.mailfrom');
//						}
						$authormail = version_compare(JVERSION, '3.0', 'ge') ? $config->get('mailfrom') : $config->getValue('config.mailfrom');
					}
				}

				// Adds filled custom fields
				$customfields = icagendaCustomfields::getListNotEmpty($data->id);

 				$custom_fields = '';

				$newline = ($defaultemail == '0') ? "<br />" : "\n";

				if ($customfields)
				{
					foreach ($customfields AS $customfield)
					{
						$cf_value = isset($customfield->cf_value) ? $customfield->cf_value : JText::_('IC_NOT_SPECIFIED');
						$custom_fields.= $customfield->cf_title . ": " . $cf_value . $newline;
					}
				}

				// MAIL
				$replacements = array(
					"\\n"				=> "\n",
					'[SITENAME]'		=> $sitename,
					'[SITEURL]'			=> $siteURL,
					'[AUTHOR]'			=> $authorname,
					'[AUTHOREMAIL]'		=> $authormail,
					'[CONTACTEMAIL]'	=> $contactemail,
					'[TITLE]'			=> $title,
//					'[EVENTID]'			=> is_numeric($data->eventid) ? (int) $data->eventid : null,
					'[EVENTURL]'		=> $urlevent,
					'[NAME]'			=> $name,
					'[EMAIL]'			=> $email,
					'[PHONE]'			=> $phone,
					'[PLACES]'			=> $people,
					'[CUSTOMFIELDS]'	=> $custom_fields,
//					'[NOTES]'			=> $notes ? $notes : JText::_('COM_ICAGENDA_NOT_SPECIFIED'),
					'[NOTES]'			=> $notes,
					'[DATE]'			=> $regDate,
					'[TIME]'			=> $regTime,
					'[DATETIME]'		=> $regDateTime,
					'[STARTDATE]'		=> $startD,
					'[ENDDATE]'			=> $endD,
					'[STARTDATETIME]'	=> $regStartDateTime,
					'[ENDDATETIME]'		=> $regEndDateTime,
				);

				foreach ($replacements as $key => $value)
				{
					$subject = str_replace($key, $value, $subject);
					$body = str_replace($key, $value, $body);
					$adminsubject = str_replace($key, $value, $adminsubject);
					$adminbody = str_replace($key, $value, $adminbody);
				}

				// Set Sender of USER and ADMIN emails
				$mailer = JFactory::getMailer();
				$adminmailer = JFactory::getMailer();

				// Joomla 3.x / 2.5 SWITCH
				if(version_compare(JVERSION, '3.0', 'ge'))
				{
					$mailfrom = $config->get('mailfrom');
					$fromname = $config->get('fromname');
				}
				else
				{
					$mailfrom = $config->getValue('config.mailfrom');
					$fromname = $config->getValue('config.fromname');
				}

				$mailer->setSender(array( $mailfrom, $fromname ));
				$adminmailer->setSender(array( $mailfrom, $fromname ));

				// Set Recipient of USER email
				$user = JFactory::getUser();

				if (!isset($data->email))
				{
					$recipient = $user->email;
				}
				else
				{
					$recipient = $data->email;
				}

				$mailer->addRecipient($recipient);

				// Set Recipient of ADMIN email
				$admin_array = array();

				if (in_array('0', $emailAdminSend_select))
				{
					array_push($admin_array, $mailfrom);
				}

				if (in_array('1', $emailAdminSend_select))
				{
					array_push($admin_array, $authormail);
				}

				if (in_array('2', $emailAdminSend_select))
				{
					$customs_emails = explode(',', $emailAdminSend_custom);
					$customs_emails = str_replace(' ','',$customs_emails);

					foreach ($customs_emails AS $cust_mail)
					{
						array_push($admin_array, $cust_mail);
					}
				}

				if (in_array('3', $emailAdminSend_select))
				{
					array_push($admin_array, $contactemail);
				}

				$adminrecipient = $admin_array;
				$adminmailer->addRecipient($adminrecipient);

				// Set Subject of USER and ADMIN email
				$mailer->setSubject($subject);
				$adminmailer->setSubject($adminsubject);

				// Set Body of USER and ADMIN email
				if ($defaultemail == 0)
				{
					// HTML custom notification email send to user
					$mailer->isHTML(true);
					$mailer->Encoding = 'base64';
				}

				$adminbody = str_replace("<br />", "\n", $adminbody);

				$mailer->setBody($body);
				$adminmailer->setBody($adminbody);

				// Optional file attached
//				$mailer->addAttachment(JPATH_COMPONENT.DS.'assets'.DS.'document.pdf');

				// Send USER email confirmation, if enabled
				if ($emailUserSend == 1)
				{
					if ( isset($data->email) )
					{
						$send = $mailer->Send();
					}
				}

				// Send ADMIN email notification, if enabled
				if ($emailAdminSend == 1)
				{
					if ((isset($data->eventid)) AND ($data->eventid != '0') AND ($data->name != NULL))
					{
						$sendadmin = $adminmailer->Send();
					}
				}


				if ($alreadyexist == 'no')
				{
					// get the application object
					$app = JFactory::getApplication();

					// redirect after successful registration
					$app->redirect(htmlspecialchars_decode($urllist) , ''. JText::_( 'COM_ICAGENDA_REGISTRATION_TY' ).' '.$data->name.', '.JText::sprintf( 'COM_ICAGENDA_REGISTRATION', $title ).'<br />'.$periodd.' (<a href="'.$urlevent.'">'. JText::_( 'COM_ICAGENDA_REGISTRATION_EVENT_LINK' ).'</a>)');
				}

			}
			else
			{
				// get the application object
				$app = JFactory::getApplication();

				// redirect after successful registration
				$app->redirect(htmlspecialchars_decode($urlregistration) , JText::_( 'COM_ICAGENDA_REGISTRATION_EMAIL_NOT_VALID' ));

				return false;
			}

		}
		else
		{
			JError::raiseError('404', JTEXT::_('JERROR_LAYOUT_PAGE_NOT_FOUND'));

			return false;
		}
	}


	/**
	 * ESSENTIAL FUNCTIONS
	 */

	// FUNCTION TO CHECK IF NEXT MOVE (to be moved!)
	protected function ctrlNext ($i)
	{
		return $i;
	}

	// Function to convert font color, depending on category color
	function fontColor($i)
	{
		$color = isset($i->cat_color) ? $i->cat_color : '';

		$hex_R	= substr($color, 1, 2);
		$hex_G	= substr($color, 3, 2);
		$hex_B	= substr($color, 5, 2);
		$RGBhex	= hexdec($hex_R) . ',' . hexdec($hex_G) . ',' . hexdec($hex_B);

		$RGB	= explode(',', $RGBhex);
		$RGBa	= $RGB[0];
		$RGBb	= $RGB[1];
		$RGBc	= $RGB[2];

		$somme	= ($RGBa + $RGBb + $RGBc);

		if ($somme > '600')
		{
			$fcolor = 'fontColor';
		}
		else
		{
			$fcolor = '';
		}

		return $fcolor;
	}

	private function validEmail($email)
	{
		$isValid	= true;
		$atIndex	= strrpos($email, "@");

		if (is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain		= substr($email, $atIndex+1);
			$local		= substr($email, 0, $atIndex);
			$localLen	= strlen($local);
			$domainLen	= strlen($domain);

			if ($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			elseif ($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			elseif ($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			elseif (preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
			}
			elseif (preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
			{
				// character not valid in local part unless
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
				{
					$isValid = false;
				}
			}

			// Check the domain name
			if ($isValid
				&& !$this->is_valid_domain_name($domain))
			{
				return false;
			}

			// Uncomment below to have PHP run a proper DNS check (risky on shared hosts!)
			/**
			if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
				// domain not found in DNS
				$isValid = false;
			}
			/**/
		}

		return $isValid;
	}


	// Check if a domain is valid
	function is_valid_domain_name($domain_name)
	{
		$pieces = explode(".", $domain_name);

		foreach ($pieces as $piece)
		{
			if (!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $piece)
				|| preg_match('/-$/', $piece))
			{
				return false;
			}
		}

		return true;
	}


	// Url to add to Google Calendar
	protected function gcalendarUrl ($i)
	{
		$text			= $i->title.' ('.$i->cat_title.')';
		$details		= $i->desc;
		$venue			= $i->place_name;
		$s_dates		= $i->dates;
		$single_dates	= unserialize($s_dates);
		$website		= $this->Event_Link($i);

		$location	= $venue ? $venue.' - '.$i->address : $i->address;

		$get_date	= '';
		$href		= '#';

		if (JRequest::getVar('date'))
		{
			// if 'All Dates' set
			$get_date = JRequest::getVar('date');
		}
		else
		{
			// if 'Only Next/Last Date' set
			$get_date = date('Y-m-d-H-i', strtotime($i->next));
		}

		$ex			= explode('-', $get_date);
		$this_date	= $ex['0'] . '-' . $ex['1'] . '-' . $ex['2'] . ' ' . $ex['3'] . ':' . $ex['4'];
		$startdate	= date('Y-m-d-H-i', strtotime($i->startdate));
		$enddate	= date('Y-m-d-H-i', strtotime($i->enddate));

		if ($this->periodTest($i)
			&& ($get_date >= $startdate)
			&& ($get_date <= $enddate)
			&& (!in_array($this_date, $single_dates))
			)
		{
			$ex_S	 = explode('-', $startdate);
			$ex_E	 = explode('-', $enddate);
			$dateday = $ex_S['0'] . $ex_S['1'] . $ex_S['2'] . 'T' . $ex_S['3'] . $ex_S['4'];
			$dateday.= '00/' . $ex_E['0'] . $ex_E['1'] . $ex_E['2'] . 'T' . $ex_E['3'] . $ex_E['4'] . '00';

		}
		else
		{
			$dateday = $ex['0'] . $ex['1'] . $ex['2'] . 'T' . $ex['3'] . $ex['4'];
			$dateday.= '00/' . $ex['0'] . $ex['1'] . $ex['2'] . 'T' . $ex['3'] . $ex['4'] . '00';
		}

		// Get the site name
		$config = JFactory::getConfig();

		// Joomla 3.x / 2.5 SWITCH
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$sitename = $config->get('sitename');
		}
		else
		{
			$sitename = $config->getValue('config.sitename');
		}

		$href = 'http://www.google.com/calendar/event?action=TEMPLATE';

		$mbString			= extension_loaded('mbstring');
		$text				= $mbString ? mb_substr($text, 0, 100, 'UTF-8') : substr($text, 0, 100);
		$len				= strrpos($text, ' ');  // interruption on a space
		$text				= substr($text, 0, $len);

		$href.= '&text=' . urlencode($text) . '...';
		$href.= '&dates=' . $dateday;
		$href.= '&location=' . urlencode($location);
		$href.= '&trp=true';

		$limit_reduc		= '37'; // 37 chars (&trp=true&details=&sf=true&output=xml)
		$limit_notlogged	= '785';
		$lenpart			= strlen($href);
		$lenlast			= 2068 - $lenpart - $limit_reduc - $limit_notlogged; // max link length minus (title+location)
		$details			= urlencode(strip_tags($details));
		$details			= substr($details, 0 , $lenlast);
		$len				= strrpos($details, '+');
		$details			= substr($details, 0 , $len);

		$href.= '&details=' . substr($details, 0, $lenlast) . '...';

		return $href;
	}


	// Url to add to Yahoo Calendar
	protected function yahoocalendarUrl ($i)
	{
		$text			= $i->title.' ('.$i->cat_title.')';
		$details		= $i->desc;
		$venue			= $i->place_name;
		$s_dates		= $i->dates;
		$single_dates	= unserialize($s_dates);
		$website		= $this->Event_Link($i);

		$location	= $venue ? $venue.' - '.$i->address : $i->address;
		$get_date	= '';
		$href		= '#';
		$endday		= '';

		if (JRequest::getVar('date'))
		{
			// if 'All Dates' set
			$get_date = JRequest::getVar('date');
		}
		else
		{
			// if 'Only Next/Last Date' set
			$get_date = date('Y-m-d-H-i', strtotime($i->next));
		}

		$ex			= explode('-', $get_date);
		$this_date	= $ex['0'] . '-' . $ex['1'] . '-' . $ex['2'] . ' ' . $ex['3'] . ':' . $ex['4'];
		$startdate	= date('Y-m-d-H-i', strtotime($i->startdate));
		$enddate	= date('Y-m-d-H-i', strtotime($i->enddate));

		if ($this->periodTest($i)
			&& $get_date >= $startdate
			&& $get_date <= $enddate
			&& !in_array($this_date, $single_dates)
			)
		{
			$ex_S = explode('-', $startdate);
			$ex_E = explode('-', $enddate);

			if ($this->displaytime($i) == 1)
			{
				$dateday	= $ex_S['0'] . $ex_S['1'] . $ex_S['2'] . 'T' . $ex_S['3'] . $ex_S['4'] . '00';
				$endday		= $ex_E['0'] . $ex_E['1'] . $ex_E['2'] . 'T' . $ex_E['3'] . $ex_E['4'] . '00';
			}
			else
			{
				$dateday	= $ex_S['0'] . $ex_S['1'] . $ex_S['2'];
				$endday		= $ex_E['0'] . $ex_E['1'] . $ex_E['2'];
			}
		}
		else
		{
			if ($this->displaytime($i) == 1)
			{
				$dateday = $ex['0'] . $ex['1'] . $ex['2'] . 'T' . $ex['3'] . $ex['4'] . '00';
			}
			else
			{
				$dateday = $ex['0'] . $ex['1'] . $ex['2'];
			}
		}

		// Shortens the description, if more than 1000 characters
		$lengthMax			= '1000';
		$details			= urlencode(strip_tags($details));
		$details			= substr($details, 0, $lengthMax);
		$shortenedDetails	= strrpos($details, '+');
		$details			= substr($details, 0, $shortenedDetails);

		$href = "http://calendar.yahoo.com/?v=60";
		$href.= "&VIEW=d";
		$href.= "&in_loc=" . urlencode($location);
		$href.= "&type=20";
		$href.= "&TITLE=" . urlencode($text);
		$href.= "&ST=" . $dateday;
		$href.= "&REND=" . $endday;
		$href.= "&DUR=";
		$href.= "&DESC=" . substr($details, 0, $lengthMax) . '...';
		$href.= "&URL=" . urlencode($website);

		return $href;
	}


	// Url to add to Windows Live (Hotmail) Calendar
	protected function wlivecalendarUrl ($i)
	{
		$text			= $i->title.' ('.$i->cat_title.')';
		$details		= $i->desc;
		$venue			= $i->place_name;
		$s_dates		= $i->dates;
		$single_dates	= unserialize($s_dates);
		$website		= $this->Event_Link($i);

		$location	= $venue ? $venue . ' - ' . $i->address : $i->address;
		$get_date	= '';
		$href		= '#';
		$endday		= '';

		if (JRequest::getVar('date'))
		{
			// if 'All Dates' set
			$get_date = JRequest::getVar('date');
		}
		else
		{
			// if 'Only Next/Last Date' set
			$get_date = date('Y-m-d-H-i', strtotime($i->next));
		}

		$ex			= explode('-', $get_date);
		$this_date	= $ex['0'] . '-' . $ex['1'] . '-' . $ex['2'] . ' ' . $ex['3'] . ':' . $ex['4'];
		$startdate	= date('Y-m-d-H-i', strtotime($i->startdate));
		$enddate	= date('Y-m-d-H-i', strtotime($i->enddate));

		if ( $this->periodTest($i)
			&& $get_date >= $startdate
			&& $get_date <= $enddate
			&& !in_array($this_date, $single_dates)
			)
		{
			$ex_S		= explode('-', $startdate);
			$ex_E		= explode('-', $enddate);
			$dateday	= $ex_S['0'] . $ex_S['1'] . $ex_S['2'] . 'T' . $ex_S['3'] . $ex_S['4'] . '00';
			$endday		= $ex_E['0'] . $ex_E['1'] . $ex_E['2'] . 'T' . $ex_E['3'] . $ex_E['4'] . '00';

		}
		else
		{
			$dateday	= $ex['0'] . $ex['1'] . $ex['2'] . 'T' . $ex['3'] . $ex['4'] . '00';
		}

		$href = "http://calendar.live.com/calendar/calendar.aspx?rru=addevent";
		$href.= "&dtstart=" . $dateday;
		$href.= "&dtend=" . $endday;
		$href.= "&summary=" . urlencode($text);
		$href.= "&location=" . urlencode($location);

		return $href;
	}


	// Display a link to add to Google Calendar - Not in Use in Official Theme Packs (default and ic_rounded)
	protected function gcalendarLink ($i)
	{
		return '<a class="iCtip" href="' . $this->gcalendarUrl($i) . '" title="Add to Google Calendar"><img src="media/com_icagenda/images/cal/google_cal-16.png" alt="" /></a>';
	}
}
