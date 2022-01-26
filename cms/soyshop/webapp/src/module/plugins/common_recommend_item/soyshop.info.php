<?php
/*
 */
class CommonRecommendItemInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_recommend_item").'">おすすめ商品の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_recommend_item","CommonRecommendItemInfo");
?>
