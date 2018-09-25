<?php
/**
 * @class Cart05Page
 * @date 2009-07-16T17:44:33+09:00
 * @author SOY2HTMLFactory
 */
class Cart05Page extends MainCartPageBase{

	function doPost(){

		$cart = CartLogic::getCart();
		$paymentModuleId = $cart->getAttribute("payment_module");

		$paymentModule = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByPluginId($paymentModuleId);
		SOYShopPlugin::load("soyshop.payment", $paymentModule);

		SOYShopPlugin::invoke("soyshop.payment.option", array(
			"cart" => $cart,
			"mode" => "post"
		));

		soyshop_redirect_cart();
		exit;
	}

	function __construct(){
		parent::__construct();

		//completeはCompletaPage.class.phpに移動
		$cart = CartLogic::getCart();
		$paymentModule = $cart->getAttribute("payment_module");

		//Cart05Pageを開いた回数を調べる。指定の回数以上表示したら閲覧を禁止する
		if($cart->getPaymentOptionPageDisplayCount() >= SOYShop_ShopConfig::load()->getCartTryCountAndBanByIpAddress()){
			$cart->banIPAddress($paymentModule);
			soyshop_redirect_cart();
			exit;
		}

		$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		$paymentModule = $moduleDAO->getByPluginId($paymentModule);

		SOYShopPlugin::load("soyshop.payment", $paymentModule);

		$this->addLabel("option_page", array(
			"html" => SOYShopPlugin::display("soyshop.payment.option", array(
				"cart" => $cart
			))
		));

		SOYShopPlugin::load("soyshop.cart");
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page05",
			"cart" => $cart
		));

		$html = $delegate->getHtml();

		$this->addModel("has_cart_plugin", array(
			"visible" => (count($html) > 0)
		));

		$this->createAdd("cart_plugin_list", "_common.CartPluginListComponent", array(
			"list" => $html
		));
	}
}
