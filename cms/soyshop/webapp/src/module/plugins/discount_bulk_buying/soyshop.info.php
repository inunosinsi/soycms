<?php
/**
 * プラグイン インストール画面
 */
class DiscountBulkBuyingInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=discount_bulk_buying").'">設定画面へ</a>';
		}else{
			return "";
		}
	}

}

SOYShopPlugin::extension("soyshop.info", "discount_bulk_buying", "DiscountBulkBuyingInfo");
?>