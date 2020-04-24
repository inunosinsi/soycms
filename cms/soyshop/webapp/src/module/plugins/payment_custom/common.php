<?php
/*
 * Created on 2011/07/18
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class PaymentCustomCommon{
	public static function getCustomConfig(){
    	return SOYShop_DataSets::get("payment_custom", array(
    		"name" => "",
    		"description" => "",
    		"mail" => "支払方法：***",
    		"price" => 0,
    		"status" => "2"
    	));
    }
}

?>
