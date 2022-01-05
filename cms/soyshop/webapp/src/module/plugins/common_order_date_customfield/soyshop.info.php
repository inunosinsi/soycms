<?php
/*
 */
class CommonOrderDateCustomfieldInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_order_date_customfield") . '">オーダーカスタムフィールド(日付)の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_order_date_customfield", "CommonOrderDateCustomfieldInfo");
