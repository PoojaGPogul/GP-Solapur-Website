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
 * @since       3.2.8
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modelitem');
jimport( 'joomla.html.parameter' );
jimport( 'joomla.registry.registry' );

jimport('joomla.user.helper');
jimport('joomla.access.access');

class iCModeliChelper extends JModelItem
{
	// SubTitle Events list
	public static function iCheader($total, $getpage, $arrowtext, $number_per_page, $pagination)
	{
		// loading iCagenda PARAMS
		$app		= JFactory::getApplication();
		$iCparams	= $app->getParams();

		$time = $iCparams->get('time', '1');
		$headerList = $iCparams->get('headerList', '');

		if ($time == '0')
		{
			// COM_ICAGENDA_ALL
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_ALL_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_ALL_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_ALL_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_ALL_NO_EVENT' );
		}
		elseif ($time == '1')
		{
			// COM_ICAGENDA_OPTION_TODAY_AND_UPCOMING
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_NO_EVENT' );
		}
		elseif ($time == '2')
		{
			// COM_ICAGENDA_OPTION_PAST
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_PAST_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_PAST_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_PAST_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_PAST_NO_EVENT' );
		}
		elseif ($time == '3')
		{
			// COM_ICAGENDA_OPTION_FUTURE
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_UPCOMING_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_UPCOMING_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_UPCOMING_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_UPCOMING_NO_EVENT' );
		}
		elseif ($time == '4')
		{
			// COM_ICAGENDA_OPTION_TODAY
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_NO_EVENT' );
		}

		$report = $report2 = '';

		if ($total == 1)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_one . '</span>';
		}
		if ($total == 0)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_noevt . '</span>';
		}
		if ($total > 1)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_many . '</span>';
		}

		$num = $number_per_page;

		// No display if number does not exist
		if ($num == NULL)
		{
			$pages = 1;
		}
		else
		{
			$pages = ceil($total/$num);
		}

		$page_nb = $getpage;

		if (JRequest::getVar('page') == NULL)
		{
			$page_nb = 1;
		}

		if ($pages <= 1)
		{
			$report2.= '';
		}
		else
		{
			$report2.= ' <span class="ic-subtitle-pages"> - '.JText::_( 'COM_ICAGENDA_EVENTS_PAGE' ).' '.$page_nb.' / '.$pages.'</span>';
		}

		// Tag for header title depending of show_page_heading setting
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu()->getActive();

    	if (is_object($menuItem)
    		&& $menuItem->params->get('show_page_heading', 1))
    	{
			$tag = 'h2';
		}
		else
		{
			$tag = 'h1';
		}

		// Display Header title/subtitle (options)
		if ($headerList == 1)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<' . $tag . ' class="ic-header-title">' . $header_title . '</' . $tag . '>';
			$header.= '<div class="ic-header-subtitle">' . $report . ' ' . $report2 . '</div>';
		}
		elseif ($headerList == 2)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<' . $tag . ' class="ic-header-title">' . $header_title . '</' . $tag . '>';
		}
		elseif ($headerList == 3)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<div class="ic-header-subtitle">' . $report . ' ' . $report2 . '</div>';
		}
		elseif ($headerList == 4)
		{
			$header = '<div>';
		}

		$header.='</div>';
		$header.= '<br/>';

		return $header;
	}

	// Navigator Events list
	public static function pagination ($count_items, $getpage, $arrowtext, $number_per_page, $pagination)
	{
		// If number of pages < or = 1, no display of pagination
		if (($count_items / $number_per_page) <= 1)
		{
			$nav = '';
		}
		else
		{
			// first check whether there are elements of those selected
			$ctrlNext = ($count_items > $number_per_page) ? 1 : NULL;
			$ctrlBack = ($getpage && $getpage > 1) ? 1 : NULL;

			$num = $number_per_page;

			// No display if number not exist
			$pages = ($num == NULL) ? 1 : ceil($count_items / $number_per_page);

			$nav = '<div class="navigator">';

			// in the case of text next/prev
			$textnext = ($arrowtext == 1) ? JText::_( 'JNEXT' ) : '';
			$textback = ($arrowtext == 1) ? JText::_( 'JPREV' ) : '';

			$parentnav = JRequest::getInt('Itemid');

			$mainframe = JFactory::getApplication();
			$isSef = $mainframe->getCfg( 'sef' );

			if ($isSef == '1')
			{
				$urlpage=JRoute::_(JURI::current().'?');
			}
			elseif ($isSef == '0')
			{
				$urlpage='index.php?option=com_icagenda&view=list&Itemid='.(int)$parentnav.'&';
			}

			if ($pages >= 2)
			{
				if ($ctrlBack != NULL)
				{
					if ($getpage && $getpage<$pages) {
						$pageBack=$getpage-1;
						$pageNext=$getpage+1;
						$nav.='<a class="icagenda_back iCtip" href="'.JRoute::_($urlpage.'page='.$pageBack).'" title="'.$textback.'"><span aria-hidden="true" class="iCicon iCicon-backic"></span> '.$textback.'&nbsp;</a>';
						$nav.='<a class="icagenda_next iCtip" href="'.JRoute::_($urlpage.'page='.$pageNext).'" title="'.$textnext.'">&nbsp;'.$textnext.' <span aria-hidden="true" class="iCicon iCicon-nextic"></span></a>';
					}
					else {
						$pageBack=$getpage-1;
						$nav.='<a class="icagenda_back iCtip" href="'.JRoute::_($urlpage.'page='.$pageBack).'" title="'.$textback.'"><span aria-hidden="true" class="iCicon iCicon-backic"></span> '.$textback.'&nbsp;</a>';
					}
				}
				if ($ctrlNext!=NULL){
					if(!$getpage){
						$pageNext=2;
					}
					else{
						$pageNext=$getpage+1;
						$pageBack=$getpage-1;
					}
					if (empty($pageBack)) {
						$nav.='<a class="icagenda_next iCtip" href="'.JRoute::_($urlpage.'page='.$pageNext).'" title="'.$textnext.'">&nbsp;'.$textnext.' <span aria-hidden="true" class="iCicon iCicon-nextic"></span></a>';
					}
				}
			}

			if ($pagination == 1) {

				/* Pagination */

				if (empty($pageBack)) {
					$nav.='<div style="text-align:left">[ ';
				} elseif (($getpage && $getpage==$pages)){
					$nav.='<div style="text-align:right">[ ';
				} else {
					$nav.='<div style="text-align:center">[ ';
				}

				/* Boucle sur les pages */
				for ($i = 1 ; $i <= $pages ; $i++) {

					if ($i==1 || (($getpage-5) < $i && $i < ($getpage+5)) || $i==$pages)
					{
						if ($i == $pages && $getpage < ($pages-5))
						{
							$nav.= '...';
						}

						if ($i == $getpage)
						{
							$nav.= ' <b>' . $i . '</b>';
						}
						else
						{
							$nav.= ' <a href="' . $urlpage . 'page=' . $i . '"';
							$nav.= ' class="iCtip"';
							$nav.= ' title="' . JText::sprintf( 'COM_ICAGENDA_EVENTS_PAGE_PER_TOTAL', $i, $pages ) . '">';
							$nav.= $i;
							$nav.= '</a>';
						}

						if ($i == 1 && $getpage > 6)
						{
							$nav.= '...';
						}
					}
				}

				$nav.=' ]</div>';
			}

			$nav.='</div>';
		}

		return $nav;
	}


	protected static function iCparam ($param){
		// Import params
		$app = JFactory::getApplication();
		$iCparams = $app->getParams();
		$iCparam = $iCparams->get($param);

		return $iCparam;
	}

	// Function to get Format Date (using option format, and translation)
	public static function formatDate ($d)
	{
		$iCModeliChelper = new iCModeliChelper();
		$mkt_date= $iCModeliChelper->mkt($d);

		// get Format
		$for = '0';
		// Global Option for Date Format
		$date_format_global = JComponentHelper::getParams('com_icagenda')->get('date_format_global', 'Y - m - d');
		$date_format_global = $date_format_global ? $date_format_global : 'Y - m - d';

		$for = $iCModeliChelper->iCparam('format');

		// default
		if (($for == NULL) OR ($for == '0'))
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
		$exformat = explode (' ', $for);
		$format='';
		$separator = ' ';

		// Day with no 0 (test if Windows server)
		$dayj = '%e';
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    		$dayj = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $dayj);
		}

		// Date Formatting using strings of Joomla Core Translations (update 3.1.4)
		$dateFormat=date('d-M-Y', $mkt_date);
		$separator = $iCModeliChelper->iCparam('date_separator');
		foreach($exformat as $k=>$val){
			switch($val){

				// day (v3)
				case 'd': $val=date("d", strtotime("$dateFormat")); break;
				case 'j': $val=strftime("$dayj", strtotime("$dateFormat")); break;
				case 'D': $val=JText::_(date("D", strtotime("$dateFormat"))); break;
				case 'l': $val=JText::_(date("l", strtotime("$dateFormat"))); break;
				case 'dS': $val=strftime(stristr(PHP_OS,"win") ? "%#d" : "%e", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;
				case 'jS': $val=strftime("$dayj", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;

				// month (v3)
				case 'm': $val=date("m", strtotime("$dateFormat")); break;
				case 'F': $val=JText::_(date("F", strtotime("$dateFormat"))); break;
				case 'M': $val=JText::_(date("F", strtotime("$dateFormat")).'_SHORT'); break;
				case 'n': $val=date("n", strtotime("$dateFormat")); break;

				// year (v3)
				case 'Y': $val=date("Y", strtotime("$dateFormat")); break;
				case 'y': $val=date("y", strtotime("$dateFormat")); break;

				// separators of the components (v2)
				case '*': $val=$separator; break;
				case '_': $val=' '; break;
				case '/': $val='/'; break;
				case '.': $val='.'; break;
				case '-': $val='-'; break;
				case ',': $val=','; break;
				case 'the': $val='the'; break;
				case 'gada': $val='gada'; break;
				case 'de': $val='de'; break;
				case 'г.': $val='г.'; break;
				case 'den': $val='den'; break;
				case '&#1088;.': $val = '&#1088;.'; break;



				// day
				case 'N': $val=strftime("%u", strtotime("$dateFormat")); break;
				case 'w': $val=strftime("%w", strtotime("$dateFormat")); break;
				case 'z': $val=strftime("%j", strtotime("$dateFormat")); break;

				// week
				case 'W': $val=date("W", strtotime("$dateFormat")); break;

				// month
				case 'n': $val=$separator.date("n", strtotime("$dateFormat")).$separator; break;

				// time
				case 'H': $val=date("H", strtotime("$dateFormat")); break;
				case 'i': $val=date("i", strtotime("$dateFormat")); break;


				default: $val=''; break;
			}
			if($k!=0)$format.=''.$val;
			if($k==0)$format.=$val;
		}
		return $format;
	}


	// Set Date format for url
	public static function EventUrlDate($evt)
	{
		$replace = array("-", " ", ":");
		$datedayclean = str_replace($replace, "", $evt);
		$evt_explode = explode(' ', $evt);
		$dateday = $evt_explode['0'].'-'.str_replace(':', '-', $evt_explode['1']);
		return $dateday;
	}


	// mktime with control
	protected function mkt($data)
	{
		$data = str_replace(' ', '-', $data);
		$data = str_replace(':', '-', $data);
		$ex_data = explode('-', $data);
		$hour = isset($ex_data['3']) ? $ex_data['3'] : '0';
		$min = isset($ex_data['4']) ? $ex_data['4'] : '0';
		$sec = '0';
		$day = isset($ex_data['2']) ? $ex_data['2'] : '0';
		$month = isset($ex_data['1']) ? $ex_data['1'] : '0';
		$year = isset($ex_data['0']) ? $ex_data['0'] : '0000';
		$ris = mktime($hour, $min, $sec, $month, $day, $year);

		return strftime($ris);
	}


	// Get Next Date (or Last Date)
	public static function nextDate ($evt, $i)
	{
		$eventTimeZone = null;

		$period			= unserialize($i->period); // returns array
		$startdatetime	= $i->startdatetime;
		$enddatetime	= $i->enddatetime;
		$weekdays		= $i->weekdays;
		$singledates	= unserialize($i->dates); // returns array

		$site_today_date	= JHtml::date('now', 'Y-m-d');
		$UTC_today_date		= JHtml::date('now', 'Y-m-d', $eventTimeZone);

		$next_date			= JHtml::date($evt, 'Y-m-d', $eventTimeZone);
		$next_datetime		= JHtml::date($evt, 'Y-m-d H:i', $eventTimeZone);

		$start_date			= JHtml::date($i->startdatetime, 'Y-m-d', $eventTimeZone);
		$end_date			= JHtml::date($i->enddatetime, 'Y-m-d', $eventTimeZone);

		// Check if date from a period with weekdays has end time of the period set in next.
		$time_next_datetime	= JHtml::date($next_datetime, 'H:i', $eventTimeZone);
		$time_startdate		= JHtml::date($i->startdatetime, 'H:i', $eventTimeZone);
		$time_enddate		= JHtml::date($i->enddatetime, 'H:i', $eventTimeZone);

		if ($next_date == $site_today_date
			&& $time_next_datetime == $time_enddate)
		{
			$next_datetime = $next_date . ' ' . $time_startdate;
		}

		if ( $period != NULL
			&& in_array($next_datetime, $period) )
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
			$nextDate.= self::formatDate($evt);
			$nextDate.= '</span>';

			if ($i->displaytime == 1)
			{
//				if (in_array($next_datetime, $singledates))
//				{
					$nextDate.= ' <span class="ic-single-starttime">' . self::eventTime($startdatetime, $i) . '</span>';

					if ( self::eventTime($startdatetime, $i) != self::eventTime($enddatetime, $i) )
					{
						$nextDate.= $separator . '<span class="ic-single-endtime">' . self::eventTime($enddatetime, $i) . '</span>';
					}
//				}
//				else
//				{
//					$nextDate.= ' <span class="ic-single-starttime">' . self::eventTime($next_datetime, $i) . '</span>';
//				}
			}

			$nextDate.= $end_span;
		}
		elseif ( $next_is_in_period
			&& ($weekdays == null) )
		{
			// Next in the period & different start/end date & no weekday selected
			$start	= '<span class="ic-period-startdate">';
			$start	.= self::formatDate($startdatetime);
			$start	.= '</span>';

			$end	= '<span class="ic-period-enddate">';
			$end	.= self::formatDate($enddatetime);
			$end	.= '</span>';

			if ($i->displaytime == 1)
			{
				$start		.= ' <span class="ic-period-starttime">' . self::eventTime($startdatetime, $i) . '</span>';
				$end		.= ' <span class="ic-period-endtime">' . self::eventTime($enddatetime, $i) . '</span>';
			}

			$nextDate = $start_span . $start . $separator . $end . $end_span;
		}
		else
		{
			// Next is a single date
			$nextDate = $start_span;
			$nextDate.= '<span class="ic-single-next">';
			$nextDate.= self::formatDate($evt);
			$nextDate.= '</span>';

			if ($i->displaytime == 1)
			{
				$nextDate.= ' <span class="ic-single-starttime">' . self::eventTime($evt, $i) . '</span>';
			}

			$nextDate.= $end_span;
		}

		return $nextDate;
	}


	public static function eventTime ($d, $i)
	{
		$eventTimeZone = null;

 		$t_time = JHtml::date($d, 'H:i', $eventTimeZone);
		$timeformat = '1';
		$timeformat = self::iCparam('timeformat');

		if ($timeformat == 1)
		{
			$lang_time = strftime("%H:%M", strtotime("$t_time"));
		}
		else
		{
			$lang_time = strftime("%I:%M %p", strtotime("$t_time"));
		}

		if (isset($i->time))
		{
			$oldtime = $i->time;
		}
		else
		{
			$oldtime = NULL;
		}

		if ($oldtime != NULL)
		{
			$time = $i->time;
		}
		else
		{
			$time = JText::_($lang_time);
		}

		$displayTime = $i->displaytime;

		if ($displayTime == 1)
		{
			return $time;
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
	public static function day ($date, $time, $item = null)
	{
		$eventTimeZone	= null;

		$this_date		= JHtml::date($date, 'Y-m-d H:i', $eventTimeZone);
		$day_date		= JHtml::date($date, 'd', $eventTimeZone);
		$day_today		= JHtml::date('now', 'd');
		$date_today		= JHtml::date('now', 'Y-m-d');

		if ($item)
		{
			$weekdays		= $item->weekdays;
			$period			= unserialize($item->period);
			$period			= is_array($period) ? $period : array();
			$is_in_period	= (in_array($this_date, $period)) ? true : false;
			$startdate		= $item->startdatetime;
			$day_startdate	= JHtml::date($startdate, 'd', $eventTimeZone);
			$enddate		= $item->enddatetime;
			$day_enddate	= JHtml::date($enddate, 'd', $eventTimeZone);
		}
		else
		{
			$weekdays		= '';
			$period			= '';
			$is_in_period	= false;
			$startdate		= $item->startdatetime;
			$day_startdate	= JHtml::date($startdate, 'd', $eventTimeZone);
			$enddate		= $item->enddatetime;
			$day_enddate	= JHtml::date($enddate, 'd', $eventTimeZone);
		}

		if ($is_in_period
			&& $weekdays == ''
			&& strtotime($startdate) <= strtotime($date_today)
			&& strtotime($enddate) >= strtotime($date_today)
//			&& in_array($time, array('1', '4'))
			)
		{
			$day = '';

			if ($day_today > $day_startdate)
			{
//				$day.= '<span style="font-size: 14px; vertical-align: middle">' . $day_startdate . '&nbsp;</span>';
//				$day.= '<span style="font-size: 16px; vertical-align: middle">&#8676;</span>';
			}
			else
			{
//				$day.= '<span style="font-size: 14px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">' . $day_startdate . '&nbsp;</span>';
//				$day.= '<span style="font-size: 16px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">&#8676;</span>';
			}

//			$day.= '<span style="border-radius: 10px; padding: 0 5px; border: 2px dotted gray;">' . $day_today . '</span>';
			$day.= '<span style="text-decoration: overline">' . $day_today . '</span>';
//			$day.= $day_today;

			if ($day_today < $day_enddate)
			{
//				$day.= '<span style="font-size: 16px; vertical-align: middle">&#8677;</span>';
//				$day.= '<span style="font-size: 14px; vertical-align: middle">&nbsp;' . $day_enddate . '</span>';
			}
			else
			{
//				$day.= '<span style="font-size: 16px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">&#8677;</span>';
//				$day.= '<span style="font-size: 14px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">' . $day_enddate . '&nbsp;</span>';
			}

			return $day;
		}
		else
		{
			return $day_date;
		}
	}

	// Day of the week, Full - From Joomla language file xx-XX.ini (eg. Saturday)
	public static function weekday ($i){
		$iCModeliChelper = new iCModeliChelper();

		$mkt_date = $iCModeliChelper->mkt($i);
		$l_full_weekday = date("l", $mkt_date);
		$weekday = JText::_($l_full_weekday);
		return $weekday;
	}

	// Day of the week, Short - From Joomla language file xx-XX.ini (eg. Sat)
	public static function weekdayShort ($i){
		$iCModeliChelper = new iCModeliChelper();

		$mkt_date = $iCModeliChelper->mkt($i);
		$l_short_weekday = date("D", $mkt_date);
		$weekdayShort = JText::_($l_short_weekday);
		return $weekdayShort;
	}


	/**
	 * MONTHS
	 */

	// Function used for special characters
	function substr_unicode($str, $s, $l = null) {
    	return join("", array_slice(
		preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
	}

	// Format Month (eg. December)
	public static function month ($i){
		$iCModeliChelper = new iCModeliChelper();

		$mkt_date = $iCModeliChelper->mkt($i);
//		$dateFormat=date('Y-m-d', $mkt_date);
//		$l_full_month = date("F", strtotime("$dateFormat"));
		$l_full_month = date("F", $mkt_date);
		$lang_month = JText::_($l_full_month);
		$month = $lang_month;
		return $month;
	}

	// Format Month Short - 3 first characters (eg. Dec.) OR Joomla Translation core
	public static function monthShort ($i){
		$iCModeliChelper = new iCModeliChelper();

		$Jcore = '1';
		$mkt_date=$iCModeliChelper->mkt($i);
		$l_full_month = date("F", $mkt_date);
		if ($Jcore == '1') {
			return $this->monthShortJoomla($i);
		}
		else {
			$lang_month = JText::_($l_full_month);
			$monthShort = $this->substr_unicode($lang_month,0,3);
			$space='';
			$point='';
			if(strlen($l_full_month)>3){
				$space='&nbsp;';
				$point='.';
			}
			return $space.$monthShort.$point;
		}
	}

	// Format Month Short Core - From Joomla language file xx-XX.ini (eg. Dec)
	public static function monthShortJoomla ($i){
		$iCModeliChelper = new iCModeliChelper();

		$mkt_date=$iCModeliChelper->mkt($i);
		$l_full_month = date("F", $mkt_date);
		$monthShortJoomla = JText::_($l_full_month.'_SHORT');
		return $monthShortJoomla;
	}

	// Format Month Numeric - (eg. 07)
	public static function monthNum ($i){
		$iCModeliChelper = new iCModeliChelper();

		return $iCModeliChelper->formatDate($i, 'm');
	}


	/**
	 * YEAR
	 */

	// Format Year Numeric - (eg. 2013)
	public static function year ($i){
		$iCModeliChelper = new iCModeliChelper();

		return date('Y', $iCModeliChelper->mkt($i));
	}

	// Format Year Short Numeric - (eg. 13)
	public static function yearShort ($i){
		$iCModeliChelper = new iCModeliChelper();

		return date('y', $iCModeliChelper->mkt($i));
	}

	/**
	 * TIME
	 */

	// Format Time (eg. 00:00)
	public static function evtTime($evt, $time_format, $oldtime = null)
	{
		$eventTimeZone	= null;

		$date_time		= strtotime(JHtml::date($i->next, 'Y-m-d H:i', $eventTimeZone));
 		$time_format	= 'H:i';
 		$t_time			= date($time_format, $date_time);

		$timeformat		= $this->options['timeformat'];

		if ($timeformat == 1)
		{
			$lang_time = strftime("%H:%M", strtotime("$t_time"));
		}
		else
		{
			$lang_time = strftime("%I:%M %p", strtotime("$t_time"));
		}

		$time = ($oldtime != NULL) ? $oldtime : JText::_($lang_time);

		if ($this->displaytime($i) == 1)
		{
			return $time;
		}
		else
		{
			return NULL;
		}
	}


	// Read More Button
	public static function readMore ($url, $desc, $content = ''){
		$limit = '100';
		$iCparams = JComponentHelper::getParams('com_icagenda');
		$limitGlobal = $iCparams->get('limitGlobal', 0);

		if ($limitGlobal == 1) {
			$limit = $iCparams->get('ShortDescLimit');
		}
		if ($limitGlobal == 0) {
			$customlimit=$iCparams->get('limit');
			if (is_numeric($customlimit)){
				$limit=$customlimit;
			} else {
				$limit = $iCparams->get('ShortDescLimit');
			}
		}
		if (is_numeric($limit)) {
			$limit = $limit;
		} else {
			$limit = '1';
		}
		$readmore='';
		if ($limit <= 1) {
			$readmore='';
		} else {
			$readmore=$content;
		}
		$text=preg_replace('/<img[^>]*>/Ui', '', $desc);
		if(strlen($text)>$limit){
			$string_cut=substr($text, 0,$limit);
			$last_space=strrpos($string_cut,' ');
			$string_ok=substr($string_cut, 0,$last_space);
			$text=$string_ok.' ';
			$url=$url;
			$text='<a href="'.$url.'" class="more">'.$readmore.'</a>';
		}else{
			$text='';
		}
		return $text;
	}

	/**
	 * Returns the element of a SQL query WHERE clause to support filtering the selection of Events using Event Features
	 *
	 * Controlled by menu parameters:
	 *  features_filter - array of Feature IDs
	 *  features_incl_excl - indicates whether the Feature IDs are to be used to include or exclude Events
	 *  features_any_all - indicates whether any Feature ID or all Feature IDs required to include or exclude an Event
	 *
	 * One or more sub-queries is referenced in a WHERE clause with IN() or NOT IN() to include or exclude Events.
	 *
	 * If any Feature ID in isolation is to include or exclude Event records then a single sub-query is used that
	 * uses a simple inner join between the feature and feature_xref tables to identify the distinct set of Event IDs
	 * linked to any one of the spacific Feature IDs.
	 *
	 * If all Feature IDs combined are required to include or exclude Events then separate sub-queries are used for
	 * each of the spacific Feature IDs. For this case, a more efficient option is available involving a direct join
	 * with either an inner or outer join, according to whther records are being included or excluded but this
	 * puts an unreasonable constraint on the overall syntax of the query.
	 */
	public static function getFeaturesFilter()
	{
		// get the application object
		$app = JFactory::getApplication();
		$iCmenuParams = $app->getParams();

		// Initialise a return value that can be included harmlessly in a WHERE clause, if necessary
		$filter = ' TRUE ';
		$featureids = $iCmenuParams->get('features_filter', '');

		if (is_array($featureids) && !empty($featureids))
		{
			$db = Jfactory::getDbo();
			$incl_excl = $iCmenuParams->get('features_incl_excl', '1') == '1' ? '' : 'NOT';

			if ($iCmenuParams->get('features_any_all', '1') == '1')
			{
				// Any single Feature ID will include or exclude events
				// Create comma separated list of Feature IDs
				$featureids = implode(',', $featureids);
				// Create a single sub-query
				$sub_query = $db->getQuery(true);
				$sub_query->select('fx.event_id')
					->from('#__icagenda_feature_xref AS fx')
					->innerJoin("#__icagenda_feature AS f ON fx.feature_id=f.id AND f.state=1 AND f.show_filter=1 AND f.id IN($featureids)");
				// Join the sub-query to the main query
				$filter = "(e.id $incl_excl IN(" . (string) $sub_query . '))';
			}
			else
			{
				// All Feature IDs combined will include or exclude events
				// Create a separate sub-query for each of the Feature IDs
				$sub_queries = array();

				foreach ($featureids as $featureid)
				{
					$sub_query = $db->getQuery(true);
					$sub_query->select('fx.event_id')
						->from('#__icagenda_feature_xref AS fx')
						->innerJoin("#__icagenda_feature AS f ON fx.feature_id=f.id AND f.state=1 AND f.show_filter=1 AND f.id=$featureid");
					$sub_queries[] = "e.id $incl_excl IN(" . (string) $sub_query . ')';
				}

				// Combine the sub-queries depending on inclusion or exclusion of events
				$filter = "(" . implode($incl_excl == 'NOT' ? " \nOR " : " \nAND ", $sub_queries) . ')';
			}
		}

		return $filter;
	}
}
