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
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");	//軽減税率用

		//インボイス 適格請求書発行事業者登録モード
		if(!defined("INVOICE_NUMBER_MODE")) define("INVOICE_NUMBER_MODE", false);

		$order = soyshop_get_order_object($this->id);

		/*** 注文情報 ***/
		$this->createAdd("continuous_print", "InvoiceListComponent", array(
			"list" => array($order),
			"config" => OrderInvoiceCommon::getConfig()
		));

		SOY2Logic::createInstance("module.plugins.order_invoice.logic.LogLogic")->save($order);
	}
}
