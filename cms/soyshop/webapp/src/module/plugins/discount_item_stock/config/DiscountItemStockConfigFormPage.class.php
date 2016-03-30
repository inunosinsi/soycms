<?php

class DiscountItemStockConfigFormPage extends WebPage{
	
	private $configObj;
	
	function DiscountItemStockConfigFormPage(){
		SOY2::imports("module.plugins.discount_item_stock.component.*");
		SOY2::import("module.plugins.discount_item_stock.util.DiscountItemStockUtil");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			$discountLogic = SOY2Logic::createInstance("module.plugins.discount_item_stock.logic.DiscountLogic");
			$configs = $discountLogic->convertConfigArray($_POST["Config"]);
			
			DiscountItemStockUtil::saveConfig($configs);
			
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		WebPage::WebPage();
				
		$this->addForm("form");
		
		$this->createAdd("plan_list", "DiscountPlanListComponent", array(
			"list" => DiscountItemStockUtil::getConfig()
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj= $configObj;
	}
}
?>