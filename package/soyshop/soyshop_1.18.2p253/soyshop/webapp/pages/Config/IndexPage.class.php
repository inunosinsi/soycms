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

		$this->createAdd("plugin_list", "PluginList", array(
    		"list" => $this->getPluginList(),
    		"configPageLink" => SOY2PageController::createLink("Config.Detail")
    	));

    }

    function getPluginList(){
    	$array = array();

    	SOYShopPlugin::load("soyshop.config");

		$delegate = SOYShopPlugin::invoke("soyshop.config", array(
			"mode" => "list"
		));

		$list = $delegate->getList();

		//無い場合は隠す
		if(count($list) < 1) DisplayPlugin::hide("plugin");

		return $list;
    }
}

class PluginList extends HTMLList{

	private $configPageLink;

	protected function populateItem($entity,$key){
		$this->addLink("config_page_link", array(
			"text" => $entity["title"],
			"link" => $this->configPageLink . "?plugin=" . $key
		));

		$this->addLabel("config_page_description", array(
			"html" => $entity["description"],
			"visible" => (strlen($entity["description"]) > 0)
		));
	}

	function getConfigPageLink() {
		return $this->configPageLink;
	}
	function setConfigPageLink($configPageLink) {
		$this->configPageLink = $configPageLink;
	}
}
?>