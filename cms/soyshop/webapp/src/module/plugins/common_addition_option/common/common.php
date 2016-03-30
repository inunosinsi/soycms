<?php
/*
 * Created on 2011/08/29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class CommonAdditionCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("addition_option", array(
			"name" => "",
			"price" => 0,
			"text" => ""
    	));
	}
}
?>
