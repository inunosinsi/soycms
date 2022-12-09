<?php
/*
 */
class ResearchPageClassFileConfigInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=research_page_class_file") . '">クラスファイル調査プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "research_page_class_file", "ResearchPageClassFileConfigInfo");
