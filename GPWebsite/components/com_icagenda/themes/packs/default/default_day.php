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
 * @themepack	default
 * @template	calendar info-tip
 * @version 	3.4.0 2014-11-19
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die(); ?>

<?php // Day with event ?>
<?php if ($stamp->events) {?>

	<?php // Main Background of a day ?>

	<div class="icevent <?php echo $multi_events; ?>" style="background:<?php echo $bg_day; ?> !important; z-index:1000;">

		<?php // Color of date text depending of the category color ?>
		<a>
		<div class="<?php echo $stamp->ifToday; ?> <?php echo $bgcolor; ?>" data-cal-date="<?php echo $stamp->this_day; ?>">
			<?php echo $stamp->Days; ?>
		</div>
		</a>

		<?php // Start of the Tip ?>
		<span class="spanEv">

			<?php foreach($events as $e){

				// Show image if exist
				if ($e['image']) {
					echo '<span style="background: '.$e['cat_color'].';" class="img"><img src="'.$e['image'].'" alt="" /></span>';
				}
				else {
					echo '<span style="background: '.$e['cat_color'].';" class="img"><div class="noimg '.$bgcolor.'">'.$e['no_image'].'</div></span>';
				}

				// Display Title (with link to event) and other infos if set (city, country)
				echo '<span><div class="titletip"><a href="'.$e['url'].'" rel="nofollow">&rsaquo; '.$e['title'].'</a></div>';

				// Display feature icons, if required
				if (!empty($e['features_icon_size'])) : ?>
				<div class="ic-features-container">
					<?php foreach ($e['features'] as $icon) : ?>
					<div class="ic-feature-icon">
						<img src="<?php echo $e['features_icon_root'] . $icon['icon'] ?>" alt="<?php echo $icon['icon_alt'] ?>" title="<?php echo $e['show_icon_title'] == '1' ? $icon['icon_alt'] : '' ?>">
					</div>
					<?php endforeach ?>
				</div>
				<?php endif;

				// Display Time (start) for each date
				if ($e['displaytime']) {
					echo $e['time'];
				}

				// Display Venue Name, City and/or Country for each date
				if ($e['place'] OR $e['city'] OR $e['country']) {
					echo '<div class="infotip">';
					// Display Venue Name
					if ($e['place'] AND ($e['city'] OR $e['country']) ) {
						echo $e['place'].', ';
					} else {
						echo $e['place'];
					}
					// Display City and/or Country for each date
					if ($e['city']) {
						echo $e['city'];
					}
					if (($e['country']) && ($e['city'])) {
						echo ', '.$e['country'];
					}
					if (($e['country']) AND (!$e['city'])) {
						echo $e['country'];
					}
					echo '</div>';
				}

				// Display Short Description
				if ($e['descShort']) {
					echo '<div class="infotip">'.$e['descShort'].'</div>';
				}

				// Display Registration Information
				echo '<div style="clear:both"></div>';
				if (($e['maxTickets']) || ($e['registered'])) {
					echo '<div class="regButtons">';
					if ($e['maxTickets']) {
						echo '<span class="iCreg available">'.JText::_( 'MOD_ICCALENDAR_SEATS_NUMBER' ).': '.$e['maxTickets'].'</span>';
					}
					if ($e['TicketsLeft'] AND $e['maxTickets']) {
						echo '<span class="iCreg ticketsleft">'.JText::_( 'MOD_ICCALENDAR_SEATS_AVAILABLE' ).': '.$e['TicketsLeft'].'</span>';
					}
					if ($e['registered']) {
						echo '<span class="iCreg registered">'.JText::_( 'MOD_ICCALENDAR_ALREADY_REGISTERED' ).': '.$e['registered'].'</span>';
					}
					echo '</div>';
				}

				echo '</span><span class="clr"></span>';
			}
			?>
		</span>

		<?php // Display Date at the top of the info-tip ?>
		<span class="date">
			<span class="datetxt"><?php echo JTEXT::_('JDATE');  ?> : </span>&nbsp;<span class="dateformat"><?php echo $stamp->dateTitle; ?></span>
		</span>

	</div><?php // end of the day ?>


<?php // Day with no event ?>
<?php }else{ ?>
	<div class="<?php echo $stamp->ifToday; ?>" data-cal-date="<?php echo $stamp->this_day; ?>">
		<?php echo $stamp->Days; ?>
	</div>
<?php } ?>
