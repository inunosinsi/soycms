<?php
/*
 * Created on 2012/05/24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class UtilMobileCheckUtil{
	
	public static function getConfig(){

		return SOYShop_DataSets::get("util_mobile_check.config", array(
			"prefix" => "mb",
			"prefix_i" => "i",
			"css" => 1,
			"cookie" => 0,
			"session" => 5,
			"url" => soyshop_get_site_url() . "mb/item/list",
			"message" => "Go to Mobile Site",
			"redirect" => 1,
			"redirect_iphone" => 0,
			"redirect_ipad" => 0
		));
	}
	
}
 
?>