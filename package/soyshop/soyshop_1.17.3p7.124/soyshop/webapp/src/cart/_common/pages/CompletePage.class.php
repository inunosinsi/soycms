<?php
/**
 * @class CompletePage
 * @date 2009-07-16T17:44:33+09:00
 * @author SOY2HTMLFactory
 */
class CompletePage extends MainCartPageBase{

	function doPost(){
		SOY2PageController::redirect(SOYSHOP_SITE_URL);
		exit;
	}

	function __construct(){
		WebPage::__construct();

		$cart = CartLogic::getCart();

		//注文完了
		if(!$cart->orderComplete()){

			//失敗したら？
			$cart->clearAttribute("order_id", null);
			$cart->setAttribute("page", "Cart04");

			//redirect
			soyshop_redirect_cart();
			exit;
		}

		$id = $cart->getAttribute("order_id");

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			$order = $orderDAO->getById($id);
		}catch(Exception $e){
			$order = new SOYShop_Order();
		}


		$this->addForm("order_form");

		$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber(),
		));
		$this->addLabel("order_rawid", array(
			"text" => $order->getId(),
		));

		$this->addLabel("next_page", array(
			"name" => "next_page",
			"value" => "",
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_site_url(true)
		));
		
		SOYShopPlugin::load("soyshop.cart");
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "complete",
			"cart" => $cart
		));

		$html = $delegate->getHtml();
		
		$this->addModel("has_cart_plugin", array(
			"visible" => (count($html) > 0)
		));
		
		$this->createAdd("cart_plugin_list","_common.CartPluginListComponent", array(
			"list" => $html
		));

		//カートのクリア
		$cart->clear();
	}
}
?>