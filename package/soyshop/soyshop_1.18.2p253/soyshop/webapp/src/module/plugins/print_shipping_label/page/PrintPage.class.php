<?php

class PrintPage extends HTMLTemplatePage{

	private $id;

	function build_print(){
		SOY2::imports("module.plugins.print_shipping_label.component.*");
		
		SOY2::import("domain.order.SOYShop_ItemModule");
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		
		$this->addModel("base", array(
			"href" => SOYSHOP_BASE_URL
		));
		
		//管理画面ので設定した品名を入れる
		$pluginConfig = ShippingLabelUtil::getConfig();
		if(isset($pluginConfig["product"]) && strlen(trim($pluginConfig["product"]))){
			$_POST["ShippingProduct"] = $pluginConfig["product"];
		}
		
		$this->createAdd("continuous_print", "PrintLabelListComponent", array(
			"list" => array(self::getOrder()),
			"company" => $config->getCompanyInformation(),
			"shopname" => $config->getShopName()
		));
	}
	
	private function getOrder(){
		return SOY2Logic::createInstance("logic.order.OrderLogic")->getById($this->id);		
	}
	
	function setOrderId($id){
		$this->id = $id;
	}
}
?>