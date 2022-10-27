<?php
/*
 * Created on 2009/09/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function soymail_log(){
	SOY2::import("domain.SOYMailLog");
	
	$buff = ob_get_contents();
	SOYMailLog::add("[job]",$buff);
	
}
?>