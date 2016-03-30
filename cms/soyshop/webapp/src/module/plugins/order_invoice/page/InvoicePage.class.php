<?php
class InvoicePage extends HTMLTemplatePage{

	protected $id;
	protected $logic;
	
	function setOrderId($id){
		$this->id = $id;
	}

	function build_invoice(){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		SOY2::imports("module.plugins.order_invoice.component.*");
		
		/*** 注文情報 ***/
		$order = $this->getOrder();

		$this->createAdd("continuous_print", "InvoiceListComponent", array(
			"list" => array($order),
			"itemOrderDao" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO"),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
			"config" => OrderInvoiceCommon::getConfig()
		));
	}
	
	protected function getOrder(){
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");		
		return $orderLogic->getById($this->id);		
	}
}
?>