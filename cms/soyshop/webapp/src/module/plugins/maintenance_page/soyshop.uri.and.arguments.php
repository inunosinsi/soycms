<?php

class MaintenancePageUriAndArguments extends SOYShopUriAndArgumentsBase{

	/**
	 * @return string $uri, array $args
	 */
	function execute($uri, $args){
		// 表示設定のパターンを増やしたい → checkActive内で制御
		SOY2::import("module.plugins.maintenance_page.util.MaintenancePageUtil");
		if(!MaintenancePageUtil::checkActive()) return array(null, null);

		//取り急ぎ、同じブラウザ、別タブで管理画面にログインしている場合はnullを返す
		$session = SOY2ActionSession::getUserSession();
		if(!is_null($session->getAttribute("loginid"))) return array(null, null);

		return array("_maintenance", array());
	}
}
SOYShopPlugin::extension("soyshop.uri.and.arguments", "maintenance_page", "MaintenancePageUriAndArguments");
