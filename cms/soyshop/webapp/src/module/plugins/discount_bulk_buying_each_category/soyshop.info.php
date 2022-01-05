<?php
/**
 * プラグイン インストール画面
 */
class DiscountBulkBuyingEachCategoryInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=discount_bulk_buying_each_category").'">カテゴリ版まとめ買い割引の設定</a>';
		}else{
			return "";
		}
	}
}

SOYShopPlugin::extension("soyshop.info", "discount_bulk_buying_each_category", "DiscountBulkBuyingEachCategoryInfo");
