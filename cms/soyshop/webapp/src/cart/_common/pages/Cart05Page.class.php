<?php
/**
 * @class Cart05Page
 * @date 2009-07-16T17:44:33+09:00
 * @author SOY2HTMLFactory
 */
class Cart05Page extends MainCartPageBase{

	function doPost(){

		$cart = CartLogic::getCart();

		//在庫の確認←廃止
		// if(!self::_checkStock($cart)){
		// 	$cart->clearAttribute("order_id");
		// 	$cart->setAttribute("page", "Cart01");
		// 	$cart->save();
		// 	soyshop_redirect_cart();
		// 	exit;
		// }

		$paymentModule = soyshop_get_plugin_object($cart->getAttribute("payment_module"));
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

		//在庫の確認←廃止
		// if(!self::_checkStock($cart)){
		// 	$cart->clearAttribute("order_id");
		// 	$cart->setAttribute("page", "Cart01");
		// 	$cart->save();
		// 	soyshop_redirect_cart();
		// 	exit;
		// }

		$paymentModule = $cart->getAttribute("payment_module");

		//Cart05Pageを開いた回数を調べる。指定の回数以上表示したら閲覧を禁止する
		if($cart->getPaymentOptionPageDisplayCount() >= SOYShop_ShopConfig::load()->getCartTryCountAndBanByIpAddress()){
			$cart->banIPAddress($paymentModule);
			soyshop_redirect_cart();
			exit;
		}

		$paymentModule = soyshop_get_plugin_object($paymentModule);
		SOYShopPlugin::load("soyshop.payment", $paymentModule);

		$this->addLabel("option_page", array(
			"html" => SOYShopPlugin::display("soyshop.payment.option", array(
				"cart" => $cart,
				"moduleId" => $paymentModule->getPluginId()	//Cart05ページで指定していない支払い方法が出力されないように厳重に確認する
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

	//在庫を確認して、必要であればCart01ページにリダイレクトする
	private function _checkStock(CartLogic $cart){
		try{
			$cart->checkOrderable(false);
			$cart->checkItemCountInCart();
		}catch(SOYShop_StockException $e){
			return false;
		}catch(Exception $e){
			//DB error?
		}
		return true;
	}
}
