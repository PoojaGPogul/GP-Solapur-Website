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
 * @version     3.4.1 2015-01-18
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

// iCagenda Class control (Joomla 2.5/3.x)
if(!class_exists('iCJView')) {
   if(version_compare(JVERSION,'3.0.0','ge')) {
      class iCJView extends JViewLegacy {};
   } else {
      jimport('joomla.application.component.view');
      class iCJView extends JView {};
   }
}

/**
 * HTML View class - iCagenda.
 */
class icagendaViewList extends iCJView
{
	// TODO: check and remove
	protected $return_page;

	protected $data;
	protected $getAllDates;
	protected $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app			= JFactory::getApplication();
		$document		= JFactory::getDocument();
		$this->params	= $app->getParams();

		// loading data
		$this->data			= $this->get('Data');
		$this->getAllDates	= $this->get('AllDates');
		$this->form			= $this->get('Form'); // Registration Form

		$params	= $this->params;

		// Menu Options
		$this->atlist			= $params->get('atlist', 0);
		$this->template			= $params->get('template');
		$this->title			= $params->get('title');
		$this->number			= $params->get('number', 5);
		$this->orderby			= $params->get('orderby', 2);
		$this->time				= $params->get('time', 1);

		// Component Options
		$this->iconPrint_global			= $params->get('iconPrint_global', 0);
		$this->iconAddToCal_global		= $params->get('iconAddToCal_global', 0);
		$this->iconAddToCal_options		= $params->get('iconAddToCal_options', 0);
		$this->copy						= $params->get('copy');
		$this->navposition				= $params->get('navposition', 1);
		$this->arrowtext				= $params->get('arrowtext', 1);
		$this->GoogleMaps				= $params->get('GoogleMaps', 1);
		$this->pagination				= $params->get('pagination', 1);
		$this->day_display_global		= $params->get('day_display_global', 1);
		$this->month_display_global		= $params->get('month_display_global', 1);
		$this->year_display_global		= $params->get('year_display_global', 1);
		$this->time_display_global		= $params->get('time_display_global', 0);
		$this->venue_display_global		= $params->get('venue_display_global', 1);
		$this->city_display_global		= $params->get('city_display_global', 1);
		$this->country_display_global	= $params->get('country_display_global', 1);
		$this->shortdesc_display_global	= $params->get('shortdesc_display_global', '');
		$this->statutReg				= $params->get('statutReg', 0);
		$this->dates_display			= $params->get('datesDisplay', 1);
		$this->reg_captcha				= $params->get('reg_captcha', 1);

		$this->cat_description	= ($params->get('displayCatDesc_menu', 'global') == 'global')
								? $params->get('CatDesc_global', '0')
								: $params->get('displayCatDesc_menu', '');

		$cat_options			= ($params->get('displayCatDesc_menu', 'global') == 'global')
								? $params->get('CatDesc_checkbox', '')
								: $params->get('displayCatDesc_checkbox', '');
		$this->cat_options		= is_array($cat_options) ? $cat_options : array();

		$this->pageclass_sfx	= htmlspecialchars($params->get('pageclass_sfx'));

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$vcal = $app->input->get('vcal');

		if ($vcal)
		{
			$tpl = 'vcal';
		}

		// Process the content plugins.
		JPluginHelper::importPlugin('content');

		if (version_compare(JVERSION, '3.0', 'ge')) // J3
		{
			$this->dispatcher	= JEventDispatcher::getInstance();
		}
		else // J2.5
		{
			$this->dispatcher	= JDispatcher::getInstance();
		}

		$this->_prepareDocument();

		parent::display($tpl);

		icagendaEvents::isListOfEvents();
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway 	= $app->getPathway();
		$title 		= null;

		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description', ''))
		{
			$this->document->setDescription($this->params->get('menu-meta_description', ''));
		}

		if ($this->params->get('menu-meta_keywords', ''))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords', ''));
		}

		if ($app->getCfg('MetaTitle') == '1' && $this->params->get('menupage_title', ''))
		{
			$this->document->setMetaData('title', $this->params->get('page_title', ''));
		}
	}
}
