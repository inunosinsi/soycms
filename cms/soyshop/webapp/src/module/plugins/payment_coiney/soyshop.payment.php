<?php
SOY2::import("module.plugins.payment_coiney.util.CoineyUtil");
class CoineyPaymentModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){
		$config = CoineyUtil::getConfig();

		$module = new SOYShop_ItemModule();
		$module->setId("payment_coiney");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName($this->getName());
		$module->setIsVisible(false);
		$module->setPrice(0);

		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("payment_coiney", "支払方法", $module->getName());
	}

	function getName(){
		$config = CoineyUtil::getConfig();
		if(isset($config["sandbox"]) && $config["sandbox"] == 1){
			return "STORES決済(テストモード)で支払い";
		}
		return "クレジットカード支払い";
	}

	function getDescription(){
		return "クレジットカードで支払います。";
	}

	function hasOptionPage(){
		return true;
	}

	function getOptionPage(){

		//cancel
		if(isset($_GET["cancel"])){
			$this->getCart()->setAttribute("page", "Cart04");
			soyshop_redirect_cart();
		}

		//if completed
		if(isset($_GET["complete"])){
			self::_orderComplete();
		}

		//出力
		SOY2::import("module.plugins.payment_coiney.option.CoineyOptionPage");
		$form = SOY2HTMLFactory::createInstance("CoineyOptionPage");
		$form->setCart($this->getCart());
		$form->execute();
		echo $form->getObject();
	}

	private function _orderComplete(){
		$cart = $this->getCart();
		$order = soyshop_get_order_object($cart->getAttribute("order_id"));

		//支払を完了する
		$order->setAttribute("payment_coiney.id", array(
			"name" => "STORES決済: ID",
			"value" => $cart->getAttribute("coiney_id"),
			"readonly" => true,
			"hidden" => true,
		));

		//支払いステータスの変更。現時点ではCoineyペイジの方はopenしかない
		$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);
		$cart->setAttribute("page", "Complete");
		soyshop_redirect_cart();
		exit;
	}

	function onPostOptionPage(){}
}

SOYShopPlugin::extension("soyshop.payment",			"payment_coiney", "CoineyPaymentModule");
SOYShopPlugin::extension("soyshop.payment.option",	"payment_coiney", "CoineyPaymentModule");
