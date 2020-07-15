<?php

/**
 * 通知イベント(決済など)
 * @param string $pluginId $_GET["soyshop_notification"]
 */
function execute_notification_action($pluginId){
	$paymentModule = soyshop_get_plugin_object($pluginId);
	if(!is_null($paymentModule->getId())){
		SOYShopPlugin::load("soyshop.notification", $paymentModule);
		SOYShopPlugin::invoke("soyshop.notification");
	}
}

/**
 * カートの禁止イベント
 * @param string $pluginId $_GET["soyshop_ban"]
 */
function execute_ban_action($pluginId){
	CartLogic::getCart()->banIPAddress($pluginId);
	return "OK";
}

/**
 * カート実行
 */
function execute_cart_application($args){
	SOY2::import("base.site.pages.SOYShop_CartPage");
	$webPage = SOY2HTMLFactory::createInstance("SOYShop_CartPage", array(
		"arguments" => array(SOYSHOP_CURRENT_CART_ID)
	));

	if(count($args) > 0 && $args[0] == "operation"){
		$webPage->doOperation();
		exit;
	}else{

		SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
		SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");

		SOYShopPlugin::load("soyshop.site.onload");
		SOYShopPlugin::invoke("soyshop.site.onload", array("page" => $webPage));

		$webPage->common_execute();

		SOYShopPlugin::load("soyshop.site.beforeoutput");
		SOYShopPlugin::invoke("soyshop.site.beforeoutput", array("page" => $webPage));

		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();

		SOYShopPlugin::load("soyshop.site.user.onoutput");
		$delegate = SOYShopPlugin::invoke("soyshop.site.user.onoutput", array("html" => $html));
		$html = $delegate->getHtml();

		echo $html;
	}
}
