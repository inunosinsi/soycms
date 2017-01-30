<?php

class ContinuousPage extends HTMLTemplatePage{

	private $orders;
	private $logic;
	
	function setOrders($orders){
		$this->orders = $orders;
	}
	
	function build_print(){
		SOY2::imports("module.plugins.print_shipping_label.component.*");
		
		SOY2::import("domain.order.SOYShop_ItemModule");
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		
		$this->addModel("base", array(
			"href" => SOYSHOP_BASE_URL
		));
		
		$this->createAdd("continuous_print", "PrintLabelListComponent", array(
			"list" => $this->orders,
			"company" => $config->getCompanyInformation(),
			"shopname" => $config->getShopName()
		));
	}
}
?>