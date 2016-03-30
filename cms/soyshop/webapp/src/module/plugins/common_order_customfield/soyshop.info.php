<?php
/*
 */
class CommonOrderCustomfieldInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_order_customfield") . '">オーダーカスタムフィールドの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_order_customfield", "CommonOrderCustomfieldInfo");
?>
