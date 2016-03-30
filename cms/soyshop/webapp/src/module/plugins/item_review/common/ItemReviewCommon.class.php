<?php

class ItemReviewCommon{

	public static function getConfig(){
    	return SOYShop_DataSets::get("item_review.config", array(
    		"code" => "146685",
    		"nickname" => "名無しさん",
			"login" => 1,
    		"publish" => 1,
    		"edit" => 1,
    		"point" => 0
    	));
    }
	
}
?>