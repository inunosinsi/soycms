<?php
/*
 * soyshop.order.mailbuilder.php
 * Created: 2010/02/04
 */

SOY2::import("logic.mail.SOYShop_MailBuilder");

class SOYShopOrderMailBuilder implements SOY2PluginAction,SOYShop_MailBuilder{

	function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user){
		return null;
	}
	function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user){
		return null;
	}

	function printColumn($str, $pos = "right", $width = 10){

    	$strWidth = mb_strwidth($str);

    	if($pos == "right"){
    		$size = max(0, $width - $strWidth);
    		$return = str_repeat(" ", $size);

	    	return $return . $str;
    	}

    	else if($pos == "center"){
    		$size = (int)(max(0, $width - $strWidth) / 2);
    		$return = str_repeat(" ", $size);

    		return $return . $str . $return;
    	}

    	else if($pos == "left"){
    		$size = max(0, $width - $strWidth);
	    	$return = str_repeat(" ", $size);

			return $str . $return;
    	}

		return $str;
	}
}
class SOYShopOrderMailBuilderDeletageAction implements SOY2PluginDelegateAction{

	private $builder = null;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($action instanceof SOYShop_MailBuilder){
			$this->builder = $action;
		}
	}

	function getBuilder(){
		return $this->builder;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.mailbuilder", "SOYShopOrderMailBuilderDeletageAction");
