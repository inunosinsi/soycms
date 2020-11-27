<?php
/*
 */
class CampaignInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=campaign").'">キャンペーンプラグインの設定</a>';
			return implode("\r\n", $html);
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "campaign", "CampaignInfo");
