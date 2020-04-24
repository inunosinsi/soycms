<?php
/*
 */
class ItemReviewInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_review") . '">商品レビュープラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "item_review", "ItemReviewInfo");
?>