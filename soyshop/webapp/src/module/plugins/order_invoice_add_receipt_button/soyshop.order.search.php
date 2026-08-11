<?php

SOY2::import("module.plugins.order_invoice.component.CustomSearchFormComponent");
class OrderInvoiceAddReceiptSearch extends SOYShopOrderSearch{

	const PLUGIN_ID = "order_invoice_add_receipt_button";
	const PROP_NAME = "orderReceiptStart";

	function setParameter(array $params){
		list($queries, $binds) = CustomSearchFormComponent::parameter($params, self::PLUGIN_ID, self::PROP_NAME, "receipt");
		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems(array $params){
		return array("label" => "領収書", "form" => CustomSearchFormComponent::buildSearchForm($params, self::PLUGIN_ID, self::PROP_NAME));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptSearch");
