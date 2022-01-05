<?php
/*
 */
class ItemInfoEditInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_info_edit") . '">商品情報編集ボタン設置プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "item_info_edit", "ItemInfoEditInfo");
