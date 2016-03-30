<?php
/*
 */
class TwitterProductCardsInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=twitter_product_cards").'">Twitter Product Cardの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","twitter_product_cards","TwitterProductCardsInfo");
?>