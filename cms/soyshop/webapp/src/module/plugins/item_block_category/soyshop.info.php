<?php
/*
 */
class ItemBlockCategoryInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_block_category") . '">カテゴリ商品ブロック生成プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "item_block_category", "ItemBlockCategoryInfo");
