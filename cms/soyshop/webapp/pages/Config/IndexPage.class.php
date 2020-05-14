<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

    	//商品カスタムフィールドのリンクの表示
    	$this->addModel("is_customfield", array(
    		"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_customfield"))
    	));

    	//ユーザカスタムフィールドのリンクの表示
    	$this->addModel("is_user_customfield", array(
    		"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"))
    	));

    	//ダウンロード販売モードの時に表示する
    	$this->addModel("is_download", array(
    		"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("download_assistant"))
    	));
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
