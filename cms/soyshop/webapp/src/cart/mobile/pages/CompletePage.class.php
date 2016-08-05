<?php
/**
 * @class CompletePage
 * @date 2009-07-16T17:44:33+09:00
 * @author SOY2HTMLFactory
 */
class CompletePage extends MobileCartPageBase{

	function doPost(){
		SOY2PageController::redirect(SOYSHOP_SITE_URL);
		exit;
	}

	function __construct(){
		WebPage::WebPage();

		$cart = CartLogic::getCart();

		//注文完了
		if(!$cart->orderComplete()){

			//失敗したら？
			$cart->clearAttribute("order_id", null);
			$cart->setAttribute("page", "Cart04");

			$param = null;
			if(isset($_GET[session_name()])){
				$param = session_name() . "=" . session_id();
			}
			soyshop_redirect_cart($param);
			exit;
		}

		$id = $cart->getAttribute("order_id");

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			$order = $orderDAO->getById($id);
		}catch(Exception $e){
			$order = new SOYShop_Order();
		}


		$this->createAdd("order_form","HTMLForm");

		$this->createAdd("order_id","HTMLLabel", array(
			"text" => $order->getTrackingNumber(),
		));
		$this->createAdd("order_rawid","HTMLLabel", array(
			"text" => $order->getId(),
		));

		$this->createAdd("next_page","HTMLInput", array(
			"name" => "next_page",
			"value" => "",
		));

		$this->createAdd("top_link","HTMLLink", array(
			"link" => SOYSHOP_SITE_URL
		));
		
		SOYShopPlugin::load("soyshop.cart");
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "complete",
			"cart" => $cart
		));

		$html = $delegate->getHtml();
		
		$this->createAdd("has_cart_plugin","HTMLModel", array(
			"visible" => (count($html) > 0)
		));
		
		$this->createAdd("cart_plugin_list","CartPluginList", array(
			"list" => $html
		));

		//カートのクリア
		$cart = CartLogic::getCart();
		$cart->clear();
	}
}


?>