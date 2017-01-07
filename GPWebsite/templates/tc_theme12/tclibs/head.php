<head>
<?php JHtml::_('behavior.framework', true);
if(class_exists('JHtmlJquery')) JHtml::_('jquery.framework');?>
<jdoc:include type="head" />
<script type="text/javascript">
var tcDefault = jQuery.noConflict();
jQuery(document).ready(function(e) {
(function($) {
$.fn.equalHeights = function() {
var maxHeight = 0,
$this = $(this);
$this.each( function() {
var height = $(this).innerHeight();
if ( height > maxHeight ) { maxHeight = height; }
});
return $this.css('min-height', maxHeight);
};
// auto-initialize plugin
$('[data-equal]').each(function(){
var $this = $(this),
target = $this.data('equal');
$this.find(target).equalHeights();
});
})(jQuery);
//jQuery('#tc_lbr').children().equalHeights();
if(!jQuery('.form-group').children('label[class*="col-md-"]').length)
jQuery('.form-group').children('label').addClass('col-md-3 control-label');
if(jQuery('.form-group').children('div[class*="col-md-"]').length){
jQuery('.form-group').find('input:not([type="checkbox"],[type="radio"],[type="hidden"],[type="submit"]), select').addClass('form-control');
jQuery('.form-group').find('textarea').addClass('form-control');
}
if(jQuery('.form-group').children('div:not([class*="col-md-"])')){
jQuery('.form-group').children('input:not([type="checkbox"],[type="radio"],[type="hidden"],[type="submit"]), select').addClass('form-control').wrap('<div class="col-md-5" />');
jQuery('.form-group').children('textarea').addClass('form-control').wrap('<div class="col-md-9" />');
}
});
</script>
<?php 
if($totop == 1){?>
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery(document.body).SLScrollToTop({
'text':			'Go to Top',
'title':		'Go to Top',
'className':	'scrollToTop',
'duration':		500
});
});</script>
<?php } ?>
<?php 
if($menucontrol == 1){?>
<script type="text/javascript">
var tcDefaultMenu = jQuery.noConflict();
jQuery(document).ready(function(){
jQuery("#tcdefaultmenu").oMenu({
theme: "default-menu",
effect: "fold",
mouseEvent: "hover"
});
});</script>
<?php } ?>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
</head>