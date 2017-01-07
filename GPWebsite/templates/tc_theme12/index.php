<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language;?>" >
<?php
/* @package     tc_theme12 Template
 * @author		Themescreative http://www.themescreative.com
 * @copyright	Copyright (c) 2006 - 2012 themescreative. All rights reserved
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){define('DS',DIRECTORY_SEPARATOR);}
$tcParams = '';
include_once(dirname(__FILE__).DS.'tclibs'.DS.'settings.php');
include_once(dirname(__FILE__).DS.'tclibs'.DS.'head.php');
$tcParams .= '<body id="tc" class=" logo-'.$logo.'">';
$tcParams .= '<div id="tc_wrapper" class="tc_wrapper">';
$tcParams .= TCShowModule('adverts', 'tc_xhtml', 'container');
$tcParams .=  TCShowModule('header', 'tc_xhtml', 'container');
include_once(dirname(__FILE__).DS.'tclibs'.DS.'slider.php');
$tcParams .=  TCShowModule('slider', 'tc_xhtml', 'container');
$tcParams .=  TCShowModule('top', 'tc_xhtml', 'container');
$tcParams .=  TCShowModule('info', 'tc_xhtml', 'container');
$tcParams .=  TCShowModule('maintop', 'tc_xhtml', 'container');
$tcParams .= '<main class="tc_main container clearfix">'.$component.'</main>';
$tcParams .=  TCShowModule('mainbottom', 'tc_xhtml', 'container').
TCShowModule('feature', 'tc_xhtml', 'container').
TCShowModule('bottom', 'tc_xhtml', 'container').
TCShowModule('footer', 'tc_xhtml', 'container');
$tcParams .= '<footer class="tc_wrapper_copyright tc_section">'.
'<div class="container clearfix">'.
  '<div class="col-md-12">'.($copyright ? '<div style="padding:10px;">'.$cpright.' </div>' : ''). /* You CAN NOT remove (or unreadable) this without themescreative.com permission */
'</div>'.
'</div>'.
'</footer>';
$tcParams .='</div>';	   
include_once(dirname(__FILE__).DS.'tclibs'.DS.'debug.php');
$tcParams .='</body>';
$tcParams .='</html>';
echo $tcParams;
?>