<?php

SOY2::import("module.plugins.order_invoice.component.CustomSearchFormComponent");
class OrderInvoiceSearch extends SOYShopOrderSearch{

	const PLUGIN_ID = "order_invoice";
	const PROP_NAME = "deliveryNote";

	function setParameter($params){
		list($queries, $binds) = CustomSearchFormComponent::parameter($params, self::PLUGIN_ID, self::PROP_NAME, "delivery");
		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		return array("label" => "納品書", "form" => CustomSearchFormComponent::buildSearchForm($params, self::PLUGIN_ID, self::PROP_NAME));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "order_invoice", "OrderInvoiceSearch");
