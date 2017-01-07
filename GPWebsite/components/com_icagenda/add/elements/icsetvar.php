<?php
/**
 *------------------------------------------------------------------------------
 *	iCagenda Set Var for Theme Packs
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version 	3.4.1 2015-01-14
 * @since       3.2.8
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

	// loading iCagenda PARAMS (Component + menu)
	$app		= JFactory::getApplication();
	$iCparams	= $app->getParams();
	$isSef		= $app->getCfg( 'sef' );

	$ic_view	= JRequest::getVar('view', '');
	$ic_layout	= JRequest::getVar('layout', '');


	/**
	 *	Event Header
	 */
	$BACK_ARROW				= $item->BackArrow;

	$EVENT_SHARING			= $item->share_event;
	$EVENT_REGISTRATION		= $item->reg;

	$EVENT_TITLE			= $item->title;
	$EVENT_TITLEBAR			= $item->titlebar;


	/**
	 *	Event Dates
	 */
	$TEXT_FOR_NEXTDATE		= $item->dateText;
	$EVENT_NEXT				= $item->next;
	$EVENT_NEXTDATE			= $item->nextDate;
//	$EVENT_DAY				= $item->day;
//	$EVENT_MONTHSHORT		= $item->monthShort;

	// Get var 'date_value' set to session in event details view
	$session = JFactory::getSession();
	$get_date = $session->get('date_value', '');

	if (!$get_date)
	{
		$get_date = JRequest::getVar('date');
	}

	if (isset($get_date) && !empty($get_date))
	{
		$ex = explode('-', $get_date);
		$dateday = $ex['0'].'-'.$ex['1'].'-'.$ex['2'].' '.$ex['3'].':'.$ex['4'];
	}

	$timeformat = $iCparams->get('timeformat');

	$timedisplay = '';
	$timedisplay = $item->displaytime;

	$lang_time = '';

	if (isset($get_date))
	{
		$EVENT_THIS_DATE = iCModeliChelper::formatDate($dateday);

		if ($timedisplay == 1)
		{
			if ($timeformat == 1)
			{
				$lang_time = strftime('%H:%M', strtotime($dateday));
			}
			else
			{
				$lang_time = strftime('%I:%M %p', strtotime($dateday));
			}

			$EVENT_THIS_DATE.= ' <small>' . $lang_time;

			$weekdays_array = explode (',', $item->weekdays);
			$weekdays = count($weekdays_array);

			if ( !empty($weekdays) && $item->periodTest
				&& ($lang_time != $item->endTime) )
			{
				$EVENT_THIS_DATE.= ' - ' . $item->endTime;
			}

			$EVENT_THIS_DATE.= '</small>';
		}
	}

	$datesDisplay	= $iCparams->get('datesDisplay', 1);

	if ($datesDisplay == 1)
	{
		$dates_array	= unserialize($item->dates);
		$dates_array	= is_array($dates_array) ? $dates_array : array();
		$period_array	= unserialize($item->period);
		$period_array	= is_array($period_array) ? $period_array : array();

		if ( isset($EVENT_THIS_DATE)
			&& !$item->weekdays
			&& in_array($dateday, $period_array) )
		{
			// Period with no weekdays selected
			$EVENT_VIEW_DATE_TEXT	= $TEXT_FOR_NEXTDATE;
			$EVENT_VIEW_DATE		= $EVENT_NEXTDATE;
		}
		elseif ( isset($EVENT_THIS_DATE)
			&& (in_array($dateday, $dates_array) || in_array($dateday, $period_array)) )
		{
			// Single Date or date in a period with weekdays selection
			$EVENT_VIEW_DATE_TEXT	= JTEXT::_('COM_ICAGENDA_EVENT_DATE');
			$EVENT_VIEW_DATE		= $EVENT_THIS_DATE;
		}
		else
		{
			// Next/Last Date (if type is list of events)
			$EVENT_VIEW_DATE_TEXT	= $TEXT_FOR_NEXTDATE;
			$EVENT_VIEW_DATE		= $EVENT_NEXTDATE;
		}

		if ($ic_view == 'list' && empty($ic_layout))
		{
			if ($isSef == '1')
			{
				$EVENT_URL = $item->url.'?date='.$EVENT_SET_DATE;
			}
			else
			{
				$EVENT_URL = $item->url.'&date='.$EVENT_SET_DATE;
			}
		}
		else
		{
			$EVENT_URL = $item->url;
		}
	}
	else
	{
		$EVENT_URL				= $item->url;
		$EVENT_VIEW_DATE_TEXT	= $TEXT_FOR_NEXTDATE;
		$EVENT_VIEW_DATE		= $EVENT_NEXTDATE;
	}


	/**
	 *	Feature Icons
	 */
	$FEATURES_ICONSIZE_LIST		= $iCparams->get('features_icon_size_list');
	$FEATURES_ICONSIZE_EVENT	= $iCparams->get('features_icon_size_event');
	$SHOW_ICON_TITLE			= $iCparams->get('show_icon_title');
	// Get media path
	$params_media = JComponentHelper::getParams('com_media');
	$image_path = $params_media->get('image_path', 'images');
	$FEATURES_ICONROOT_LIST		= JUri::root() . $image_path . '/icagenda/feature_icons/' . $FEATURES_ICONSIZE_LIST . '/';
	$FEATURES_ICONROOT_EVENT	= JUri::root() . $image_path . '/icagenda/feature_icons/' . $FEATURES_ICONSIZE_EVENT . '/';
	$FEATURES_ICONS				= array();

	if (isset($item->features) && is_array($item->features)
		&& (!empty($FEATURES_ICONSIZE_LIST) || !empty($FEATURES_ICONSIZE_EVENT)))
	{
		foreach ($item->features as $feature)
		{
			$FEATURES_ICONS[] = array('icon' => $feature->icon, 'icon_alt' => $feature->icon_alt);
		}
	}


	/**
	 *	Event Image and Thumbnails
	 */
	$EVENT_IMAGE			= $item->image;
	$EVENT_IMAGE_TAG		= $item->imageTag;

	$IMAGE_LARGE = $IMAGE_MEDIUM = $IMAGE_SMALL = $IMAGE_XSMALL = '';

	if ($EVENT_IMAGE)
	{
		if (icagendaClass::isLoaded('icagendaThumb'))
		{
			$IMAGE_LARGE			= icagendaThumb::sizeLarge($item->image) ? icagendaThumb::sizeLarge($item->image) : '';
			$IMAGE_MEDIUM			= icagendaThumb::sizeMedium($item->image) ? icagendaThumb::sizeMedium($item->image) : '';
			$IMAGE_SMALL			= icagendaThumb::sizeSmall($item->image) ? icagendaThumb::sizeSmall($item->image) : '';
			$IMAGE_XSMALL			= icagendaThumb::sizeXSmall($item->image) ? icagendaThumb::sizeXSmall($item->image) : '';
			$IMAGE_LARGE_HTML		= icagendaThumb::sizeLarge($item->image) ? icagendaThumb::sizeLarge($item->image, 'imgTag') : '';
			$IMAGE_MEDIUM_HTML		= icagendaThumb::sizeMedium($item->image) ? icagendaThumb::sizeMedium($item->image, 'imgTag') : '';
			$IMAGE_SMALL_HTML		= icagendaThumb::sizeSmall($item->image) ? icagendaThumb::sizeSmall($item->image, 'imgTag') : '';
			$IMAGE_XSMALL_HTML		= icagendaThumb::sizeXSmall($item->image) ? icagendaThumb::sizeXSmall($item->image, 'imgTag') : '';
		}
		else
		{
			$IMAGE_LARGE = $IMAGE_MEDIUM = $IMAGE_SMALL = $IMAGE_XSMALL = '';
			$IMAGE_LARGE_HTML = $IMAGE_MEDIUM_HTML = $IMAGE_SMALL_HTML = $IMAGE_XSMALL_HTML = '';
		}
	}


	/**
	 *	Event Details - Description, Meta-description and Intro Text
	 */
	$EVENT_DESC				= ($item->desc || $item->shortdesc) ? true : false;
//	$EVENT_DESCRIPTION		= $item->description;
	$EVENT_META				= $item->metaAsShortDesc;

	$desc_display_event = $iCparams->get('desc_display_event', '');

	if ($desc_display_event == '1') // full desc
	{
		$EVENT_SHORTDESC	= false;
		$EVENT_DESCRIPTION	= $item->description ? $item->description : false;
	}
	elseif ($desc_display_event == '2') // short desc
	{
		$EVENT_SHORTDESC	= $item->shortDescription ? $item->shortDescription : false;
		$EVENT_DESCRIPTION	= false;
	}
	elseif ($desc_display_event == '3') // short and full desc
	{
		$EVENT_SHORTDESC	= $item->shortDescription ? $item->shortDescription : false;
		$EVENT_DESCRIPTION	= $item->description ? $item->description : false;
	}
	elseif ($desc_display_event == '0') // Hide
	{
		$EVENT_SHORTDESC	= false;
		$EVENT_DESC			= false;
		$EVENT_DESCRIPTION	= false;
	}
	else // Auto (First Full Description, if does not exist, will use Short Description if not empty)
	{
		$EVENT_SHORTDESC	= false;
		$EVENT_DESCRIPTION	= $item->description ? $item->description : $item->shortDescription;
	}


	/**
	 *	Events List - Intro Text
	 */
	$shortdesc_display_global = $iCparams->get('shortdesc_display_global', '');
	$Filtering_ShortDesc_Global = JComponentHelper::getParams('com_icagenda')->get('Filtering_ShortDesc_Global', '');

	if ($shortdesc_display_global == '1') // short desc
	{
		$EVENT_DESCSHORT	= $item->shortdesc ? $item->shortdesc : false;

		if ($EVENT_DESCSHORT)
		{
			$EVENT_DESCSHORT	= empty($Filtering_ShortDesc_Global) ? '<i>' . $EVENT_DESCSHORT . '</i>' : $EVENT_DESCSHORT;
		}
	}
	elseif ($shortdesc_display_global == '2') // Auto-Introtext
	{
		$EVENT_DESCSHORT	= $item->descShort ? $item->descShort : false;
	}
	elseif ($shortdesc_display_global == '0') // Hide
	{
		$EVENT_DESCSHORT	= false;
	}
	else // Auto (First Short Description, if does not exist, Auto-generated short description from the full description. And if does not exist, will use meta description if not empty)
	{
		$short_description = $item->shortdesc ? $item->shortdesc : $item->descShort;

		$metaAsShortDesc = $item->metaAsShortDesc;

		if ($metaAsShortDesc)
		{
			$metaAsShortDesc	= empty($Filtering_ShortDesc_Global) ? '<i>' . $metaAsShortDesc . '</i>' : $metaAsShortDesc;
		}

		$EVENT_DESCSHORT	= $short_description ? $short_description : $metaAsShortDesc;
	}

	$EVENT_INTRO_TEXT = $EVENT_DESCSHORT; // New var name since 3.4.0


	/**
	 *	Event Information
	 */
	$CUSTOM_FIELDS	= $item->loadEventCustomFields;


	/**
	 *	Event Information
	 */
	$EVENT_INFOS			= $item->infoDetails;

	$SEATS_AVAILABLE		= $item->placeLeft;
	$MAX_NB_OF_SEATS		= $item->maxNbTickets;

	$EVENT_VENUE			= $iCparams->get('venue_display_global') ? $item->place_name : false;
	$EVENT_CITY				= $iCparams->get('city_display_global') ? $item->city : false;
	$EVENT_COUNTRY			= $iCparams->get('country_display_global') ? $item->country : false;

	$EVENT_PHONE			= $item->phone;
	$EVENT_EMAIL			= $item->email;
	$EVENT_EMAIL_CLOAKING	= $item->emailLink;
	$EVENT_WEBSITE			= $item->website;
	$EVENT_WEBSITE_LINK		= $item->websiteLink;
//	$EVENT_ADDRESS			= $item->address;

	/**
	 *	Event Address
	 */
	if ($item->address)
	{
		// Create an array to separate all strings between comma in individual parts
		$EVENT_STREET			= $item->address;
		$ADDRESS_EX = explode(',', $EVENT_STREET);

		// Get the end part (usually the country)
		$END_VALUE = end($ADDRESS_EX);

		if ($EVENT_COUNTRY
			&& strpos($END_VALUE, $EVENT_COUNTRY) !== false)
		{
			$END_VALUE = prev($ADDRESS_EX);
			// Remove last value, if country is inside
			$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
		}
		else
		{
			$END_VALUE = prev($ADDRESS_EX);
			// Remove 2 last values, if country is inside the previous one before end value
			if ($EVENT_COUNTRY
				&& strpos($END_VALUE, $EVENT_COUNTRY) !== false)
			{
				$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
				$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
			}
		}
		if ($EVENT_CITY
			&& strpos($END_VALUE, $EVENT_CITY) !== false)
		{
			// Remove new last value, if city is inside
			$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
		}
		else
		{
			$END_VALUE = prev($ADDRESS_EX);
			// Remove 2 new last values, if city is inside the previous one before new end value
			if ($EVENT_CITY
				&& strpos($END_VALUE, $EVENT_CITY) !== false)
			{
				$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
				$EVENT_STREET = substr( $EVENT_STREET, 0, strripos( $EVENT_STREET, ',' ) );
			}
		}
		$EVENT_ADDRESS = '';
//		$EVENT_ADDRESS.= $EVENT_VENUE.'<br />';
		$EVENT_ADDRESS.= $EVENT_STREET.'<br />';

		if ($EVENT_CITY && $EVENT_COUNTRY)
		{
			$EVENT_ADDRESS.= $EVENT_CITY.', '.$EVENT_COUNTRY.'<br />';
		}
		elseif ($EVENT_CITY && !$EVENT_COUNTRY)
		{
			$EVENT_ADDRESS.= $EVENT_CITY.'<br />';
		}
		elseif (!$EVENT_CITY && $EVENT_COUNTRY)
		{
			$EVENT_ADDRESS.= $EVENT_COUNTRY.'<br />';
		}
	}
	else
	{
		$EVENT_ADDRESS = false;
	}


	$GOOGLEMAPS_COORDINATES	= $item->coordinate;
	$EVENT_MAP				= $item->map;

	$EVENT_SINGLE_DATES		= $item->datelistUl;
	$EVENT_PERIOD			= $item->periodDates;

	$PARTICIPANTS_DISPLAY	= $item->participantList;
	$PARTICIPANTS_HEADER	= $item->participantListTitle;
	$EVENT_PARTICIPANTS		= $item->registeredUsers;

	$EVENT_ATTACHEMENTS		= $item->file;
	$EVENT_ATTACHEMENTS_TAG	= $item->fileTag;

	$CATEGORY_TITLE			= $item->cat_title;
	$CATEGORY_COLOR			= $item->cat_color;
	$CATEGORY_FONTCOLOR		= $item->fontColor;

