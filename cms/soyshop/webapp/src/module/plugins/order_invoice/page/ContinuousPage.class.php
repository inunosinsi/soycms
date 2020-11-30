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
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");	//軽減税率用

		$this->createAdd("continuous_print", "InvoiceListComponent", array(
			"list" => $this->orders,
			"config" => OrderInvoiceCommon::getConfig()
		));

		$logic = SOY2Logic::createInstance("module.plugins.order_invoice.logic.LogLogic");
		foreach($this->orders as $order){
			$logic->save($order);
		}
	}
}
