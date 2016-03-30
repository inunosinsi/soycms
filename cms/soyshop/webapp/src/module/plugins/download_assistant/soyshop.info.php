<?php
/*
 */
class DownloadAssistantInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.DownloadConfig") . '">ダウンロード販売の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "download_assistant", "DownloadAssistantInfo");
?>