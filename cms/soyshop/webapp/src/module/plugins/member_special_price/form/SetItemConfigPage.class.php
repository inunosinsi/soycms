<?php

class SetItemConfigPage extends WebPage{
	
	private $configObj;
	private $itemId;
	
	function __construct(){
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		SOY2::import("module.plugins.member_special_price.component.SpecialPriceListComponent");
	}
	
	function execute(){
		parent::__construct();
		
		$this->createAdd("special_price_list", "SpecialPriceListComponent", array(
			"list" => MemberSpecialPriceUtil::getConfig(),
			"itemId" => $this->itemId,
			"priceLogic" => SOY2Logic::createInstance("module.plugins.member_special_price.logic.SpecialPriceLogic")
		));
				
		$this->addLink("plugin_detail_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=member_special_price")
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
	
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
?>