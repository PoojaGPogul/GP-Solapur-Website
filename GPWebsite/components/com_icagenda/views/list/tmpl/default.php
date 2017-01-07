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
 * @version 	3.4.1 2015-01-23
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>
<!--
 * - - - - - - - - - - - - - -
 * iCagenda 3.4.1 by Jooml!C
 * - - - - - - - - - - - - - -
 * @copyright	Copyright (c)2012-2015 JOOMLIC - All rights reserved.
 *
-->
<?php

// Get Application
$app = JFactory::getApplication();
$document = JFactory::getDocument();

$icsetvar			= 'components/com_icagenda/add/elements/icsetvar.php';
$someObjectArr		= (array)$this->data->items;
$control			= !empty($someObjectArr) ? true : false;
$getpage			= JRequest::getVar('page', 1);
$number_per_page	= $this->number;
$all_dates_with_id	= $this->getAllDates;
$count_all			= count($all_dates_with_id);

// Header
?>
<div id="icagenda" class="ic-list-view<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1 class="componentheading">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php
	$tpl_template_events	= JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/'.$this->template.'_events.php';
	$tpl_template_list		= JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/'.$this->template.'_list.php';
	$tpl_default_events		= JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/'.$this->template.'_events.php';
	$tpl_component_css		= JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/css/'.$this->template.'_component.css';


	// Setting component css file to load
	if ( file_exists($tpl_component_css) )
	{
		$css_component	= '/components/com_icagenda/themes/packs/'.$this->template.'/css/'.$this->template.'_component.css';
		$css_com_rtl	= '/components/com_icagenda/themes/packs/'.$this->template.'/css/'.$this->template.'_component-rtl.css';
	}
	else
	{
		$css_component	= '/components/com_icagenda/themes/packs/default/css/default_component.css';
		$css_com_rtl	= '/components/com_icagenda/themes/packs/default/css/default_component-rtl.css';
	}

	// New file to display all dates for each events
	if ( file_exists($tpl_template_events) )
	{
		$tpl_events		= $tpl_template_events;
	}
	elseif ( (!$this->template || $this->template != 'default')
		&& file_exists($tpl_template_list)
		&& $this->dates_display == 1 )
	{
		$msg = 'iCagenda '.JText::_('PHPMAILER_FILE_ACCESS').' <strong>'.$this->template.'_events.php</strong>';
		$app->enqueueMessage($msg, 'warning');
		$tpl_events		= JPATH_SITE . '/components/com_icagenda/themes/packs/default/default_events.php';
		$css_component	= '/components/com_icagenda/themes/packs/default/css/default_component.css';
	}
	elseif ( (!$this->template || $this->template != 'default')
		&& $this->dates_display != 1 )
	{
		$tpl_events		= $tpl_template_events;
	}
	else
	{
		$msg = 'iCagenda '.JText::_('PHPMAILER_FILE_OPEN').' <strong>'.$this->template.'_events.php</strong>';
		$app->enqueueMessage($msg, 'warning');

		return false;
	}

	// If theme pack is not having YOUR_THEME_events.php file, loading YOUR_THEME_list.php file to display list of events
	if ( file_exists($tpl_template_list)
		&& !file_exists($tpl_template_events) )
	{
		$tpl_list		= $tpl_template_list;
	}
	else
	{
		$tpl_list		= JPATH_SITE . '/components/com_icagenda/themes/packs/default/default_events.php';
	}

	// Add the media specific CSS to the document
	JLoader::register('iCagendaMediaCss', JPATH_ROOT . '/components/com_icagenda/helpers/media_css.class.php');
	iCagendaMediaCss::addMediaCss($this->template, 'component');

	echo '<div class="ic-clearfix">';
	echo iCModeliChelper::iCheader($count_all, $getpage, $this->arrowtext, $number_per_page, $this->pagination);

	echo '<div class="ic-header-categories ic-clearfix">';
	echo $this->loadTemplate('categories');
	echo '</div>';

	if ( in_array($this->navposition, array('0', '2')) )
	{
		echo iCModeliChelper::pagination($count_all, $getpage, $this->arrowtext, $number_per_page, $this->pagination);
	}

	echo '</div>';

	$mainframe = JFactory::getApplication();
	$isSef = $mainframe->getCfg( 'sef' );

	// To be checked
	$EVENT_NEXT = (isset($EVENT_NEXT)) ? $EVENT_NEXT : false;

	if ($control)
	{
		if (file_exists($tpl_events)
			&& count($all_dates_with_id) > 0
			)
		{
			//---------------------------
			// All Dates - List View
			//---------------------------

			// Set number of events to be displayed per page
			$index = $number_per_page * ($getpage - 1);
			$recordsToBeDisplayed = array_slice($all_dates_with_id, $index, $number_per_page, true);

			// Do for each dates to be displayed on this list of events, depending of menu and/or global options
			for ($i = 0; $i < count($all_dates_with_id); $i++)
			{
				// Get id and date for each date to be displayed
				$evt_date_id		= $all_dates_with_id[$i];
				$ex_alldates_array	= explode('_', $evt_date_id);
				$evt				= $ex_alldates_array['0'];
				$evt_id				= $ex_alldates_array['1'];

				if (in_array($evt_date_id, $recordsToBeDisplayed))
				{
					foreach ($this->data->items as $item)
					{
						if ($evt_id == $item->id)
						{
							// Load Event Data
							$EVENT_DATE			= iCModeliChelper::nextDate($evt, $item);
							$EVENT_SET_DATE		= iCModeliChelper::EventUrlDate($evt);
							$EVENT_DAY			= $this->day_display_global ? iCModeliChelper::day($evt, $this->time, $item) : false;
							$EVENT_MONTHSHORT	= $this->month_display_global ? iCModeliChelper::monthShortJoomla($evt) : false;
							$EVENT_YEAR			= $this->year_display_global ? iCModeliChelper::year($evt) : false;
							$EVENT_TIME			= ($this->time_display_global && $item->displaytime == 1)
												? icagendaEvents::dateToTimeFormat($evt)
												: false;
							$READ_MORE			= ($this->shortdesc_display_global == '' && !$item->shortdesc)
												? iCModeliChelper::readMore($item->url, $item->desc, '[&#46;&#46;&#46;]')
												: false;

							// Load Events List/Event Details common Data variables
							require $icsetvar;

							// Load Template to display Event
							require $tpl_events;
						}
					}
				}
			}
		}
		else
		{
			$stamp->items = $this->data->items;

			// Theme pack not updated
			require $tpl_list;
		}

		// List Bottom
		echo '<div>';

		if (file_exists($tpl_events))
		{
			// AddThis buttons
			if ($this->atlist && isset($item->share))
			{
				echo '<div class="share">' . $item->share . '</div><div style="clear:both"></div>';
			}
		}

		// List Bottom - Navigation & pagination
		if ( $this->navposition == '1' || $this->navposition == '2' )
		{
			echo iCModeliChelper::pagination($count_all, $getpage, $this->arrowtext, $number_per_page, $this->pagination);
		}

		echo '</div>';
		echo '<div style="clear:both">&nbsp;</div>';
	}

	$this->dispatcher->trigger('onListAfterDisplay', array('com_icagenda.list', &$item, &$this->params));
	?>
</div>

<?php
$document->addStyleSheet( JURI::base( true ) . '/components/com_icagenda/add/css/style.css' );
$document->addStyleSheet( JURI::base( true ) . '/media/com_icagenda/icicons/style.css' );
$document->addStyleSheet( JURI::base( true ) . '/media/com_icagenda/css/tipTip.css' );

// Theme pack component css
$document->addStyleSheet( JURI::base( true ) . $css_component );

// RTL css if site language is RTL
$lang = JFactory::getLanguage();

if ( $lang->isRTL()
	&& file_exists( JPATH_SITE . $css_com_rtl) )
{
	$document->addStyleSheet( JURI::base( true ) . $css_com_rtl );
}

if(version_compare(JVERSION, '3.0', 'lt'))
{
	JHTML::_('behavior.mootools');

	// load jQuery, if not loaded before (NEW VERSION IN 1.2.6)
	$scripts = array_keys($document->_scripts);
	$scriptFound = false;

	for ($i = 0; $i < count($scripts); $i++)
	{
		if (stripos($scripts[$i], 'jquery.min.js') !== false)
		{
			$scriptFound = true;
		}
		// load jQuery, if not loaded before as jquery - added in 1.2.7
		if (stripos($scripts[$i], 'jquery.js') !== false)
		{
			$scriptFound = true;
		}
	}

	// jQuery Library Loader
	if (!$scriptFound)
	{
		// load jQuery, if not loaded before
		if (!JFactory::getApplication()->get('jquery'))
		{
			JFactory::getApplication()->set('jquery', true);
			// add jQuery
			$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
			$document->addScript( JURI::base( true ) . '/components/com_icagenda/js/jquery.noconflict.js');
		}
	}
}
else
{
	JHtml::_('bootstrap.framework');
	JHtml::_('jquery.framework');
}

// Loading Script tipTip used for iCtips
JHtml::script( 'com_icagenda/jquery.tipTip.js', false, true );

// Add RSS Feeds
$menu = $app->getMenu()->getActive()->id;

$feed = 'index.php?option=com_icagenda&amp;view=list&amp;Itemid=' . (int) $menu . '&amp;format=feed';
$rss = array(
	'type'    =>  'application/rss+xml',
	'title'   =>   'RSS 2.0');

$document->addHeadLink(JRoute::_($feed.'&amp;type=rss'), 'alternate', 'rel', $rss);
