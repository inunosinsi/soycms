<?php

class ContinuousPage extends HTMLTemplatePage{

	private $orders;
	private $logic;
	
	function setOrders($orders){
		$this->orders = $orders;
	}
	
	function build_print(){
//		SOY2::import("module.plugins.order_purchase.util.OrderPurchaseUtil");
		SOY2::imports("module.plugins.order_purchase.component.*");
				
		$this->createAdd("continuous_print", "PurchaseListComponent", array(
			"list" => $this->orders,
			"itemOrderDao" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO"),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
//			"config" => OrderInvoiceCommon::getConfig()
		));
	}

}
?>