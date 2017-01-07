<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril RezŽ, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril RezŽ (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.4.0 2014-11-29
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

/**
 * Returns City from Google Maps address auto-complete field.
 */
class JFormFieldiCmap_city extends JFormField
{
	protected $type='icmap_city';

	protected function getInput()
	{
		$session = JFactory::getSession();
		$ic_submit_city = $session->get('ic_submit_city', '');

		$city_value = $ic_submit_city ? $ic_submit_city : $this->value;

		$html = '<div class="clr"></div>';
		$html.= '<label class="icmap-label">' . JText::_('COM_ICAGENDA_FORM_LBL_EVENT_CITY') . '</label> <input name="' . $this->name . '" id="locality" type="text" size="41" style="color:#777777;" value="' . $city_value . '"/>';

		// clear the data so we don't process it again
		$session->clear('ic_submit_city');

		return $html;
	}
}
