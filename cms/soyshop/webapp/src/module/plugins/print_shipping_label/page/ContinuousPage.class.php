<?php

class ContinuousPage extends HTMLTemplatePage{

	private $orders;
	private $logic;
	
	function setOrders($orders){
		$this->orders = $orders;
	}
	
	function build_print(){
//		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		SOY2::imports("module.plugins.print_shipping_label.component.*");
		
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		
		$this->createAdd("continuous_print", "PrintLabelListComponent", array(
			"list" => $this->orders,
//			"itemOrderDao" => SOY2DAOFactory::create("shop.SOYShop_ItemOrderDAO"),
//			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
			"company" => $config->getCompanyInformation(),
			"shopname" => $config->getShopName()
		));
	}

}
?>