<?php
/*
 */
class RecentlyCheckedItemsModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_recently_checked_items").'">最近表示した商品プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_recently_checked_items","RecentlyCheckedItemsModuleInfo");
?>