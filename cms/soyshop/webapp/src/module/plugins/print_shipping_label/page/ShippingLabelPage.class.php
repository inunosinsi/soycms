<?php
class ShippingLabelPage extends HTMLTemplatePage{

	protected $id;
	protected $logic;
	
	function setOrderId($id){
		$this->id = $id;
	}

	function build_label(){
//		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
		SOY2::imports("module.plugins.print_shipping_label.component.*");

		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();

		$this->createAdd("continuous_print", "PrintLabelListComponent", array(
			"list" => array(self::getOrder()),
//			"itemOrderDao" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
//			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
			"company" => $config->getCompanyInformation(),
			"shopname" => $config->getShopName()
		));
	}
	
	/*** 注文情報 ***/
	private function getOrder(){
		return SOY2Logic::createInstance("logic.order.OrderLogic")->getById($this->id);		
	}
}
?>