<?php
/*
 */
class CommonSitemapXmlInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_sitemap_xml") . '">サイトマップXMLの表示設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_sitemap_xml", "CommonSitemapXmlInfo");
?>
