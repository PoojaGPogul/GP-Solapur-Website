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
 * @version     3.4.1 2015-01-27
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

?>
<!--
 * - - - - - - - - - - - - - -
 * iCagenda 3.4.1 by Jooml!C
 * - - - - - - - - - - - - - -
 * @copyright	Copyright (c)2012-2015 JOOMLIC - All rights reserved.
 *
-->
<?php

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');

foreach ($this->data->items as $i)
{
	$item	= $i;
}

$this_state = $item->state;
$this_approval = $item->approval;
$today = time();
$this_today = strtotime(date('Y-m-d', $today));
$this_next = strtotime(date('Y-m-d', strtotime($item->next)));

// Access Control
$this_access_reg = $item->accessReg;
$user = JFactory::getUser();
$userLevels = $user->getAuthorisedViewLevels();

$app = JFactory::getApplication();

$maxReg = $item->maxReg;
$registered = $item->registered;
$tickets_left = ($maxReg - $registered);

if (($item == NULL)
	|| ($this_next < $this_today)
	|| ($this_state != 1)
	|| ($this_approval == 1)
	|| ((empty($this->statutReg) && ($item->statutReg == 0)) || ($item->statutReg == 0)) )
{
	JError::raiseError('404',JTEXT::_('JERROR_LAYOUT_PAGE_NOT_FOUND'));

	return false;
}
elseif ($tickets_left <= 0)
{
	$app->enqueueMessage(JTEXT::_('JERROR_LAYOUT_PAGE_NOT_FOUND'), 'warning');

	return false;
}
elseif (!in_array($this_access_reg, $userLevels))
{
	// Redirect to login page if no access to registration form
	$return	= base64_encode($item->iCagendaRegForm);
	$rlink	= JRoute::_('index.php?option=com_users&view=login&return='.$return, false);

	$msg = '<div>';
	$msg.= '<h2>';
	$msg.= JText::_('IC_AUTH_REQUIRED');
	$msg.= '</h2>';
	$msg.= '<div>';
	$msg.= JText::_("COM_ICAGENDA_LOGIN_TO_ACCESS_REGISTRATION_FORM");
	$msg.= '</div>';
	$msg.= '<br />';
	$msg.= '<div>';
	$msg.= '<a href="' . JRoute::_($item->Event_Link) . '" class="btn btn-default btn-small button">';
	$msg.= '<i class="iCicon iCicon-backic icon-white"></i>&nbsp;' . JTEXT::_('COM_ICAGENDA_BACK') . '';
	$msg.= '</a>';
	$msg.= '&nbsp;';
	$msg.= '<a href="index.php" class="btn btn-info btn-small button">';
	$msg.= '<i class="icon-home icon-white"></i>&nbsp;' . JTEXT::_('JERROR_LAYOUT_HOME_PAGE') . '';
	$msg.= '</a>';
	$msg.= '</div>';
	$msg.= '</div>';

	// if not login, and registration form not "public"
	$app->enqueueMessage($msg);
	$app->redirect($rlink);
}
else
{
	// prepare Document
	$document	= JFactory::getDocument();
	$menus		= $app->getMenu();
	$pathway 	= $app->getPathway();
	$title 		= null;

	$icsetvar = 'components/com_icagenda/add/elements/icsetvar.php';

	$menu = $menus->getActive();
	if ($menu)
	{
		$this->params->def('page_heading', $this->params->get('page_title', $item->title));
	}
	else
	{
		$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
	}

	$title = JText::_( 'COM_ICAGENDA_REGISTRATION_TITLE' ).' : '.$item->title;

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
	$document->setTitle($title);


	// START OF THE PAGE
	?>
	<div id="icagenda" class="ic-registration-view<?php echo $this->pageclass_sfx; ?>">

		<?php
		// load Theme and css
		if (file_exists( JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/'.$this->template.'_registration.php' ))
		{
			$tpl_registration	= JPATH_SITE . '/components/com_icagenda/themes/packs/'.$this->template.'/'.$this->template.'_registration.php';
			$css_component		= '/components/com_icagenda/themes/packs/'.$this->template.'/css/'.$this->template.'_component.css';
			$css_com_rtl		= '/components/com_icagenda/themes/packs/'.$this->template.'/css/'.$this->template.'_component-rtl.css';
		}
		else
		{
			$tpl_registration	= JPATH_SITE . '/components/com_icagenda/themes/packs/default/default_registration.php';
			$css_component		= '/components/com_icagenda/themes/packs/default/css/default_component.css';
			$css_com_rtl		= '/components/com_icagenda/themes/packs/default/css/default_component-rtl.css';
		}

		// Add the media specific CSS to the document
		JLoader::register('iCagendaMediaCss', JPATH_ROOT . '/components/com_icagenda/helpers/media_css.class.php');
		iCagendaMediaCss::addMediaCss($this->template, 'component');

		$stamp = $this->data;

		// Loads Variables for Theme files
		require_once $icsetvar;

		// Loads Header
		require_once $tpl_registration;

		$user = JFactory::getUser();
		$u_id = $user->get('id');
		$u_mail = $user->get('email');

		// logged-in Users: Name/User Name Option
		$nameJoomlaUser = JComponentHelper::getParams('com_icagenda')->get('nameJoomlaUser', 1);
		if ($nameJoomlaUser == 1)
		{
			$u_name = $user->get('name');
		}
		else
		{
			$u_name = $user->get('username');
		}

		// Autofill name and email if registered user log in
		$autofilluser = JComponentHelper::getParams('com_icagenda')->get('autofilluser', 1);
		if ($autofilluser != 1)
		{
			$u_name = '';
			$u_mail = '';
		}

		// Get Phone Options
		$phoneDisplay = JComponentHelper::getParams('com_icagenda')->get('phoneDisplay', 1);

		// Get Notes Options
		$notesDisplay = JComponentHelper::getParams('com_icagenda')->get('notesDisplay', 0);

		//$themeform = $this->template.'_form';
		$theme = $this->template;

		//$infoimg = JURI::root().'components/com_icagenda/themes/packs/'.$theme.'/images/info.png';
		$infoimg = JURI::root().'components/com_icagenda/themes/packs/default/images/info.png';

		// Global Options
		$iCparams = JComponentHelper::getParams('com_icagenda');
		$terms = $iCparams->get('terms', 0);

		// Set Tooltips
		$icTip_userID	= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_USERID' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_USERID_DESC' ) . '');
		$icTip_name		= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_NAME' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_NAME_DESC' ) . '');
		$icTip_email	= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_EMAIL' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_EMAIL_DESC' ) . '');
		$icTip_phone	= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PHONE' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PHONE_DESC' ) . '');
		$icTip_date		= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_DATE' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_DATE_DESC' ) . '');
		$icTip_period	= htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PERIOD' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PERIOD_DESC' ) . '');
		$icTip_people = htmlspecialchars('<strong>' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PEOPLE' ) . '</strong><br />' . JText::_( 'ICAGENDA_REGISTRATION_FORM_PEOPLE_DESC' ) . '');

		// Variables
		$ic_required		= ' required="true"';
		$ic_required_icon	= ' *';
		$ic_readonly		= ' readonly="true"';

		$session = JFactory::getSession();
		$ic_submit_tos	= $session->get('ic_submit_tos', '');
		$post			= $session->get('ic_registration', '');

		$post_name		= $post ? $post['name'] : '';
		$post_email		= $post ? $post['email'] : '';
		$post_phone		= $post ? $post['phone'] : '';
		$post_date		= $post ? $post['date'] : '';
		$post_period	= isset($post['period']) ? $post['period'] : '';
		$post_people	= $post ? $post['people'] : '';
		$post_notes		= isset($post['notes'])? $post['notes'] : '';
		?>

		<?php // START CONTENT ?>

		<?php // TITLE REGISTRATION ?>
		<div class="ic-form-title">
			<h1><?php echo JText::_( 'COM_ICAGENDA_REGISTRATION_TITLE' ); ?></h1>
		</div>

		<?php // ERROR ALERT ?>
		<div id="form_errors" class="alert alert-danger fade in" style="display:none">
			<strong><?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED'); ?></strong>
			<div id="message_error">
			</div>
		</div>

		<?php // FIELDS REQUIRED INFO (not used) ?>
		<div class="ic-required-info">
			<?php echo JText::_( 'COM_ICAGENDA_FORM_REQUIRED_INFO' ); ?>
		</div>

		<?php // START FORM ?>
		<form name="registration" action="<?php echo JRoute::_('index.php?option=com_icagenda'); ?>" class="icagenda_form" method="post" enctype="multipart/form-data" onsubmit="return iCheckForm();">
			<div class="fieldset">
				<?php if (($u_id) AND ($autofilluser == 1)) : ?>
					<?php echo '<input type="hidden" name="uid" value="'.$u_id.'" />'; ?>
				<?php else : ?>
					<?php echo '<input type="hidden" name="uid" value="" disabled="disabled" size="2" />'; ?>
				<?php endif; ?>

				<?php // Name Field ?>
					<?php
					$name_option = !empty($u_name) ? $ic_readonly : $ic_required;
					?>
					<div class="ic-control-group ic-clearfix">
						<div class="ic-control-label">
							<label id="reg_name-lbl"><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_NAME' ); ?><?php echo $ic_required_icon; ?></label>
						</div>
						<div class="ic-controls">
							<?php if (!$post_name) : ?>
								<?php echo '<input type="text" id="reg_name" name="name" value="'.$u_name.'"'.$name_option.' />'; ?>
							<?php else : ?>
								<?php echo '<input type="text" id="reg_name" name="name" value="'.$post_name.'"'.$name_option.' />'; ?>
							<?php endif; ?>
							<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_name . '"></span>'; ?>
						</div>
					</div>

				<?php // Email Field ?>
					<?php
					$email_required = !empty($item->emailRequired) ? $ic_required : '';
					$email_required_icon = !empty($item->emailRequired) ? $ic_required_icon : '';
					$email_readonly = !empty($u_mail) ? $ic_readonly : '';
					?>
					<div class="ic-control-group ic-clearfix">
						<div class="ic-control-label">
							<label id="reg_email-lbl"><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_EMAIL' ); ?><?php echo $email_required_icon; ?></label>
						</div>
						<div class="ic-controls">
							<?php if (!$post_email) : ?>
								<?php echo '<input type="email" id="reg_email" name="email" value="'.$u_mail.'"'.$email_required.$email_readonly.' class="required validate-email" />'; ?>
							<?php else : ?>
								<?php echo '<input type="email" id="reg_email" name="email" value="'.$post_email.'"'.$email_required.$email_readonly.' class="required validate-email" />'; ?>
							<?php endif; ?>
							<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_email . '"></span>'; ?>
						</div>
					</div>

				<?php // Phone Field ?>
				<?php if ($phoneDisplay == 1) : ?>
					<?php
					$phone_required = !empty($item->phoneRequired) ? $ic_required : '';
					$phone_required_icon = !empty($item->phoneRequired) ? $ic_required_icon : '';
					?>
					<div class="ic-control-group ic-clearfix">
						<div class="ic-control-label">
							<label id="reg_phone-lbl"><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_PHONE' ); ?><?php echo $phone_required_icon; ?></label>
						</div>
						<div class="ic-controls">
							<?php if (!$post_phone) : ?>
								<?php echo '<input type="text" id="reg_phone" name="phone" value="" size="20"'.$phone_required.' />'; ?>
							<?php else : ?>
								<?php echo '<input type="text" id="reg_phone" name="phone" value="' . $post_phone . '" size="20"'.$phone_required.' />'; ?>
							<?php endif; ?>
							<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_phone . '"></span>'; ?>
						</div>
					</div>
				<?php endif; ?>


				<?php
				/**
				 * Field Date
				 */
//				foreach($stamp->items as $item)
//				{
					$typeReg = $item->typeReg;

					//
					// All Options (Option removed in 3.3.2)
					//
					if ($typeReg == 0)
					{
						?>
						<div class="ic-control-group ic-clearfix">
							<div class="ic-control-label">
								<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_DATE' ); ?></label>
							</div>
							<div class="ic-controls">
								<select type="hidden" name="date">
									<?php
									foreach ($item->datelistMkt as $date)
									{
										$date_get = explode('@@', $date);
										$date_value = $date_get[0];
										$date_label = $date_get[1];

										$selected = ($post_date == $date_value) ? ' selected' : '';

										echo '<option value="' . $date_value . '"' . $selected . '>' . $date_label . '</option>';
									}
									?>
								</select>
								<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_date . '"></span>'; ?>
							</div>
						</div>

						<?php if ($item->periodDisplay && ($item->periodControl == 1)) : ?>
							<div class="ic-control-group ic-clearfix">
								<div class="ic-control-label">
									<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_PERIOD' ); ?></label>
								</div>
								<div class="ic-controls">
									<?php
//									foreach($stamp->items as $item)
//									{
										$start = $item->startDate.' <span class="evttime">'.$item->startTime.'</span>';
										$end = $item->endDate.' <span class="evttime">'.$item->endTime.'</span>';
										echo $start.' - '.$end;
//									}
									?>
									<div>
										<label>&nbsp;</label>
										<?php echo JText::_( 'JYES' );?> <input type="radio" name="period" value="1" />
										<?php echo JText::_( 'JNO' );?> <input type="radio" name="period" value="0" CHECKED />
										<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_period . '"></span>'; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>

					<?php
					//
					// Dates List
					//
					}
					elseif ($typeReg == 1)
					{
						?>
						<div class="ic-control-group ic-clearfix">
							<div class="ic-control-label">
								<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_DATE' ); ?></label>
							</div>
							<div class="ic-controls ic-select">
								<select type="hidden" name="date">
									<?php
									foreach ($item->datelistMkt as $date)
									{
										$date_get = explode('@@', $date);
										$date_value = $date_get[0];
										$date_label = $date_get[1];

										$selected = ($post_date == $date_value) ? ' selected' : '';

										echo '<option value="' . $date_value . '"' . $selected . '>' . $date_label . '</option>';
									}
									?>
								</select>
								<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_date . '"></span>'; ?>
							</div>
						</div>

					<?php
					//
					// Only Period
					//
					}
					else
					{
					?>
						<?php if ($item->periodDisplay && ($item->periodControl == 1)) : ?>
							<div class="ic-control-group ic-clearfix">
								<div>
									<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_PERIOD' ); ?></label>
								</div>
								<div class="ic-controls">
									<input type="hidden" name="period" value="1" />
									<?php
										$start = $item->startDate.' <span class="evttime">'.$item->startTime.'</span>';
										$end = $item->endDate.' <span class="evttime">'.$item->endTime.'</span>';
										echo $start.' - '.$end;
									?>
								</div>
							</div>
						<?php else : ?>
							<input type="hidden" name="period" value="1" />
							<!--div class="ic-control-group">
								<div class="ic-control-label">
									<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_DATE' ); ?></label>
								</div>
								<div class="ic-controls ic-select">
									<select type="hidden" name="date">
										<?php
											foreach ($item->datelistMkt as $date)
											{
												$date_get = explode('@@', $date);
												$date_value = $date_get[0];
												$date_label = $date_get[1];
												echo '<option value="'.$date_value.'">'.$date_label.'</option>';
											}
										?>
									</select>
									<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_date . '"></span>'; ?>
								</div>
							</div-->
						<?php endif; ?>
					<?php
					}
//				}

				/**
				 * Field Number of People
				 */
//				foreach ($stamp->items as $item)
//				{
					$maxRlist = $item->maxRlist;

					if ($maxRlist > 1) : ?>
						<div class="ic-control-group ic-clearfix">
							<div class="ic-control-label">
								<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_PEOPLE' ); ?></label>
							</div>
							<div class="ic-controls ic-select">
							<select type="list" name="people">
								<?php
								$maxRlist = $item->maxRlist;
								$maxReg = $item->maxReg;
								$registered = $item->registered;
								$placeRemain = ($maxReg - $registered);

								if ($placeRemain < $maxRlist)
								{
									$maxRlist = $placeRemain;
								}
								for ($i=1; $i <= $maxRlist; $i++)
								{
									$selected = ($post_people == $i) ? ' selected' : '';

									echo '<option value="'.$i.'"' . $selected . '>'.$i.'</option>';
								}
								?>
							</select>
							<?php echo '<span class="iCFormTip iCicon iCicon-info-circle" title="' . $icTip_people . '"></span>'; ?>
							</div>
						</div>
					<?php else : ?>
						<input type="hidden" name="people" value="1" />
					<?php endif; ?>
				<?php
//				}

				/**
				 * Custom Fields
				 */
				// Load Custom fields - Registration form (1)
				echo icagendaCustomfields::loader(1);

				/**
				 * Field Notes
				 */
				if ($notesDisplay == 1) : ?>
					<div class="ic-control-group ic-clearfix">
						<div class="ic-control-label">
							<label><?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_NOTES' ); ?></label>
						</div>
						<div class="ic-controls">
							<textarea name="notes" rows="10" cols="5" style="width:100%" placeholder="<?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_NOTES_DESC' ); ?>"><?php echo $post_notes; ?></textarea>
						</div>
					</div>
				<?php endif; ?>


				<?php // Hidden fields to process redirection
				$eventID = JRequest::getInt('id');
				$ItemID = JRequest::getInt('Itemid');
				$current_url = JURI::getInstance()->toString();
//				$session = JFactory::getSession();
//				$get_date = $session->get('date_value', '');
				?>
				<input type="hidden" name="event" value="<?php echo $eventID; ?>" />
				<input type="hidden" name="menuID" value="<?php echo $ItemID; ?>" />
				<input type="hidden" name="current_url" value="<?php echo $current_url; ?>" />
				<input type="hidden" name="max_nb_of_tickets" value="<?php echo $item->maxReg; ?>" />
				<input type="hidden" id="tos" name="submit_tos" value="<?php echo $ic_submit_tos; ?>" />
				<!--input type="hidden" name="event_date" value="<?php //echo $get_date; ?>" /-->

				<?php
				/**
				 * Terms of Service Display
				 */
				if ($terms == 0)
				{
					// Terms of Service not displayed
					$tokenHTML = str_replace('type="hidden"','id="formAgree" type="checkbox" checked style="display:none"',JHTML::_( 'form.token' ));
					echo $tokenHTML;
					echo '<div class="bgButton">';
				}
				elseif ($terms == 1)
				{
					// Terms of Service
					$checked = ($ic_submit_tos == 'checked') ? ' checked' : '';

					$tokenHTML = str_replace('type="hidden"','id="formAgree" type="checkbox"' . $checked, JHtml::_( 'form.token' ));

					// Get the site name
					$config = JFactory::getConfig();
					if(version_compare(JVERSION, '3.0', 'ge')) {
						$sitename = $config->get('sitename');
					} else {
						$sitename = $config->getValue('config.sitename');
					}

					// Tos Type
					$iCparams = JComponentHelper::getParams('com_icagenda');
					$terms_Type = $iCparams->get('terms_Type', '');
					$termsArticle = $iCparams->get('termsArticle', '');
					$termsContent = $iCparams->get('termsContent', '');

					$termsDEFAULT_STRING = JText::_( 'COM_ICAGENDA_REGISTRATION_TERMS');
					$termsDEFAULT = str_replace('[SITENAME]', $sitename, $termsDEFAULT_STRING);
					$termsARTICLE = 'index.php?option=com_content&view=article&id='.$termsArticle.'&tmpl=component';
					$termsCUSTOM = $termsContent;

					// Menu-item ID (fix 3.2.1.1)
					$menu = JFactory::getApplication()->getMenu();
					$menuItems = $menu->getActive();
					$menuID = $menuItems->id;
					?>
					<input type="hidden" name="menuID" value="<?php echo $menuID; ?>" />
					<div class="bgButton">
						<div>
							<b><big><?php echo JText::_( 'COM_ICAGENDA_TERMS_AND_CONDITIONS'); ?></big></b>
						</div>
						<?php
						if ($terms_Type == 1)
						{
							echo '<iframe src="'.htmlentities($termsARTICLE).'" width="98%" height="150"></iframe>';
						}
						elseif ($terms_Type == 2)
						{
							echo '<div style="padding: 25px; background:#FFF; color: #333; text-align:left">';
							echo $termsCUSTOM;
							echo '</div>';
						}
						else
						{
							echo '<div style="padding: 25px; background:#FFF; color: #333; text-align:left">';
							echo $termsDEFAULT;
							echo '</div>';
						}
						?>
						<!--iframe src="<?php echo htmlentities($tosURL); ?>" width="98%" height="150"></iframe-->
						<div class="agreeToS">
							<p><?php echo $tokenHTML; ?> <?php echo JText::_( 'COM_ICAGENDA_TERMS_AND_CONDITIONS_AGREE'); ?> *</p>
						</div>
					<?php
				}
				?>

					<?php // RECAPTCHA ?>
					<?php if ($this->reg_captcha != '0') : ?>
					<div class="ic-control-group ic-clearfix">
						<div class="ic-control-label">
							<label> </label>
						</div>
						<div class="ic-controls">
							<?php echo $this->form->getInput('captcha'); ?>
						</div>
					</div>
					<br />
					<?php endif; ?>

					<span>
						<input id="submit" type="submit" value="<?php echo JText::_( 'ICAGENDA_REGISTRATION_FORM_SUBMIT' ); ?>" class="button" name="Submit"/>
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="return" value="index.php" />
						<?php if (false) echo JHtml::_( 'form.token' ); ?>
					</span>
					<span class="buttonx">
						<a href="<?php echo $item->Event_Link; ?>" title="<?php echo JTEXT::_('COM_ICAGENDA_CANCEL'); ?>">
							<?php echo JTEXT::_('COM_ICAGENDA_CANCEL'); ?>
						</a>
					</span>
				</div><?php // End Div bgButton ?>
			</div><?php // End Form Fields ?>
			<div style="clear:both"></div>
		</form>
	</div>
	<?php
	// clear the data so we don't process it again
	$session->clear('ic_registration');
	$session->clear('custom_fields');
	$session->clear('ic_submit_tos');

	// Script validation for Registration form (1)
	$iCheckForm = icagendaForm::submit(1);
	JFactory::getDocument()->addScriptDeclaration($iCheckForm);

	// CSS files which could be overridden into your site template. (eg. /templates/my_template/css/com_icagenda/icagenda.front.css)
	JHTML::stylesheet( 'com_icagenda/tipTip.css', false, true );

	// Theme pack component css
	$document->addStyleSheet( JURI::base( true ) . $css_component );

	// RTL css if site language is RTL
	$lang = JFactory::getLanguage();

	if ( $lang->isRTL()
		&& file_exists( JPATH_SITE . $css_com_rtl) )
	{
		$document->addStyleSheet( JURI::base( true ) . $css_com_rtl );
	}

	if (version_compare(JVERSION, '3.0', 'lt'))
	{
		JHTML::_('behavior.mootools');

		// load jQuery, if not loaded before (NEW VERSION IN 1.2.6)
		$scripts = array_keys($document->_scripts);
		$scriptFound = false;
		$scriptuiFound = false;
		$mapsgooglescriptFound = false;

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
			if (stripos($scripts[$i], 'jquery-ui.min.js') !== false)
			{
				$scriptuiFound = true;
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
		if (!$scriptuiFound)
		{
			/**
			 * Change jQuery UI version from 1.9.2 to 1.8.23 (joomla version, but not complete)
			 * to prevent a conflict in tooltip that appeared since Joomla 3.1.4
			 */
//			$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
			$document->addScript( 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js' );
		}
	}
	else
	{
//		JHtml::_('formbehavior.chosen', 'select');
//		JHtml::_('behavior.formvalidation');
		JHtml::_('bootstrap.framework');
		JHtml::_('jquery.framework');
	}

	JHtml::script( 'com_icagenda/jquery.tipTip.js', false, true );
	JHtml::script( 'com_icagenda/icagenda.js', false, true );
	JHtml::script( 'com_icagenda/icform.js', false, true );

	$iCtip	 = array();
	$iCtip[] = '	jQuery(document).ready(function(){';
	$iCtip[] = '		jQuery(".iCFormTip").tipTip({maxWidth: "250px", defaultPosition: "right", edgeOffset: 10});';
	$iCtip[] = '	});';

	// Add the script to the document head.
	JFactory::getDocument()->addScriptDeclaration(implode("\n", $iCtip));
}
