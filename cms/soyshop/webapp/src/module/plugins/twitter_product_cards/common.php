<?php

class TwitterProductCardsCommon{

	public static function getConfig(){
		return SOYShop_DataSets::get("twitter_product_cards_config", array(
			"site" => "twitter",
			"creater" => "twitter"
		));
	}
}
