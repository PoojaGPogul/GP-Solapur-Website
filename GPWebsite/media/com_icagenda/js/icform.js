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
 * @version     3.4.0 2014-12-03
 * @since       3.4.0
 *------------------------------------------------------------------------------
*/

/**
 * Function Text Counter
 */
function iCtextCounter( field, countfield, maxlimit  )
{
	if ( field.value.length > maxlimit )
	{
		field.value = field.value.substring( 0, maxlimit );
		countfield.value = 0;
		jQuery(field).addClass("ic-counter-limit");
		jQuery(countfield).addClass("ic-counter-limit");

		return false;
	}
	else
	{
		countfield.value = maxlimit - field.value.length;
		jQuery(field).removeClass("ic-counter-limit");
		jQuery(countfield).removeClass("ic-counter-limit");
	}
}

/**
 * Function in array
 */
function inArray(needle, haystack)
{
	var length = haystack.length;
	for(var i = 0; i < length; i++)
	{
		if(haystack[i] == needle) return true;
	}
	return false;
}
