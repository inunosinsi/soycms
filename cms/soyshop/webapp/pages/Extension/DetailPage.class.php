<?php

class DetailPage extends WebPage{
	
	function doPost(){}
	
	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		$detailId = (isset($args[1])) ? $args[1] : null;
		try{
   			$plugin = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByPluginId($pluginId);
   		}catch(Exception $e){
			SOY2PageController::jump("");
   		}
	
		WebPage::__construct();
		
		SOYShopPlugin::load("soyshop.admin.list", $plugin);
		$exts = SOYShopPlugin::invoke("soyshop.admin.list")->getContents();
		
		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];
		
		$this->addLink("list_link", array(
			"link" => SOY2PageController::createLink("Extension." . $pluginId),
			"text" => (isset($ext["title"])) ? $ext["title"] : null
		));

		//詳細用の拡張ポイント
		SOYShopPlugin::load("soyshop.admin.detail", $plugin);
		$detail = SOYShopPlugin::invoke("soyshop.admin.detail", array("detailId" => $detailId))->getContent();
		
		$this->addLabel("page_name", array(
			"text" => (isset($detail["title"])) ? $detail["title"] : null
		));
		
		$this->addLabel("page_content", array(
			"html" => (isset($detail["content"])) ? $detail["content"] : null
		));
	}
}
?>