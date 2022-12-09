<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

    	//各リンクの出力設定
		DisplayPlugin::toggle("category", AUTH_ITEM);
		DisplayPlugin::toggle("customfield", SOYShopPluginUtil::checkIsActive("common_customfield"));
		DisplayPlugin::toggle("user_customfield", SOYShopPluginUtil::checkIsActive("common_user_customfield"));
		DisplayPlugin::toggle("download", SOYShopPluginUtil::checkIsActive("download_assistant"));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("設定");
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Config.FooterMenu.ConfigFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
