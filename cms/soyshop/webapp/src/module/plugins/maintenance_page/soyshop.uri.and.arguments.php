<?php

class MaintenancePageUriAndArguments extends SOYShopUriAndArgumentsBase{

	/**
	 * @return string $uri, array $args
	 */
	function execute($uri, $args){
		SOY2::import("module.plugins.maintenance_page.util.MaintenancePageUtil");
		if(!MaintenancePageUtil::checkActive()) return array(null, null);

		// @ToDo 表示設定のパターンを増やしたい

		return array("_maintenance", array());
	}
}
SOYShopPlugin::extension("soyshop.uri.and.arguments", "maintenance_page", "MaintenancePageUriAndArguments");
