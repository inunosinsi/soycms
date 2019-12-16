<?php
/*
 * Created on 2012/05/21
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class TwitterProductCardsCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("twitter_product_cards_config", array(
										"site" => "twitter",
										"creater" => "twitter"
									));
	}
}
?>