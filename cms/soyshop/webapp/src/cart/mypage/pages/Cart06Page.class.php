<?php
/**
 * @class Cart06Page
 * @date 2009-10-17
 * @author SOY2HTMLFactory
 */
class Cart06Page extends MainCartPageBase{

	function doPost(){

		$cart = CartLogic::getCart();
		$paymentModule = $cart->getAttribute("payment_module");

		$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		$paymentModule = $moduleDAO->getByPluginId($paymentModule);

		SOYShopPlugin::load("soyshop.payment",$paymentModule);

		 SOYShopPlugin::invoke("soyshop.payment.option", array(
			"cart" => $cart,
			"mode" => "post"
		));

		soyshop_redirect_cart();
		exit;
	}

	function Cart06Page(){
		WebPage::WebPage();

		//completeはCompletaPage.class.phpに移動
		$cart = CartLogic::getCart();
		$paymentModule = $cart->getAttribute("payment_module");

		$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		$paymentModule = $moduleDAO->getByPluginId($paymentModule);

		SOYShopPlugin::load("soyshop.payment",$paymentModule);

		$this->createAdd("option_page","HTMLLabel", array(
			"html" => SOYShopPlugin::display("soyshop.payment.option", array(
				"cart" => $cart
			))
		));
	}
}


?>