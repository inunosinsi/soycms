<?php
class PurchasePage extends HTMLTemplatePage{

	protected $id;
	protected $logic;

	function setOrderId($id){
		$this->id = $id;
	}

	function build_invoice(){
//		SOY2::import("module.plugins.order_purchase.util.OrderPurchaseUtil");
		SOY2::imports("module.plugins.order_purchase.component.*");

		/*** 注文情報 ***/
		$this->createAdd("continuous_print", "PurchaseListComponent", array(
			"list" => array(self::getOrder()),
			"itemOrderDao" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
//			"config" => OrderInvoiceCommon::getConfig()
		));
	}

	private function getOrder(){
		return SOY2Logic::createInstance("logic.order.OrderLogic")->getById($this->id);
	}
}
