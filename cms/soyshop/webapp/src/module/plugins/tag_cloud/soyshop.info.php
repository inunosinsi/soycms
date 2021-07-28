<?php
/*
 */
class TagCloudInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=tag_cloud").'">タグクラウドプラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "tag_cloud", "TagCloudInfo");
