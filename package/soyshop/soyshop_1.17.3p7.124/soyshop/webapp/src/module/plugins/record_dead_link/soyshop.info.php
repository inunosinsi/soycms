<?php
/*
 */
class RecordDeadLinkInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=record_dead_link") . '">リンク切れページのアクセス履歴の確認</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "record_dead_link", "RecordDeadLinkInfo");
?>