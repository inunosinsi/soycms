<?php

class ContinuousPage extends HTMLTemplatePage{

	private $orders;
	private $logic;
	
	function setOrders($orders){
		$this->orders = $orders;
	}
	
	function build_print(){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		SOY2::imports("module.plugins.order_invoice.component.*");
		
		$orders = $this->orders;
		
		$this->createAdd("continuous_print", "InvoiceListComponent", array(
			"list" => $orders,
			"itemOrderDao" => SOY2DAOFactory::create("shop.SOYShop_ItemOrderDAO"),
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO"),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
			"config" => OrderInvoiceCommon::getConfig()
		));
	}

}
?>