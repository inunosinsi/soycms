<?php
/*
 */
class CommonNoticeStockInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_notice_stock").'">在庫数残りわずか通知の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_notice_stock","CommonNoticeStockInfo");
?>
