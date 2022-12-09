<?php

SOY2::import("module.plugins.order_invoice.component.CustomSearchFormComponent");
class OrderInvoiceAddInvoiceSearch extends SOYShopOrderSearch{

	const PLUGIN_ID = "order_invoice_add_invoice_button";
	const PROP_NAME = "orderInvoice";

	function setParameter(array $params){
		list($queries, $binds) = CustomSearchFormComponent::parameter($params, self::PLUGIN_ID, self::PROP_NAME, "invoice");
		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems(array $params){
		return array("label" => "請求書", "form" => CustomSearchFormComponent::buildSearchForm($params, self::PLUGIN_ID, self::PROP_NAME));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice_add_invoice_button", "OrderInvoiceAddInvoiceSearch");
