<?php
/**
 * @class Cart05Page
 * @date 2009-07-16T17:44:33+09:00
 * @author SOY2HTMLFactory
 */
class Cart05Page extends MobileCartPageBase{

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

		$param = null;
		if(isset($_GET[session_name()])){
			$param = session_name() . "=" . session_id();
		}
		soyshop_redirect_cart($param);
		exit;
	}

	function Cart05Page(){
		parent::__construct();

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