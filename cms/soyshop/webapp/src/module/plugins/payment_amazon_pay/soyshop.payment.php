<?php

class AmazonPayPayment extends SOYShopPayment{

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function onSelect(CartLogic $cart){

		$module = new SOYShop_ItemModule();
		$module->setId("payment_amazon_pay");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName(self::getName());
		$module->setIsVisible(false);
		$module->setPrice(0);

		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("payment_amazon_pay", "支払方法", $module->getName());
	}

	function getName(){
		$cnf = AmazonPayUtil::getConfig();
		if(isset($cnf["sandbox"]) && $cnf["sandbox"] == 1){
			return "Amazon Pay(テストモード)で支払い";
		}
		return "Amazon Pay支払い";
	}

	function getDescription(){
		return "Amazon Payで支払います。";
	}

	function hasOptionPage(){
		return true;
	}

	function getOptionPage(){

		//戻る
		if(isset($_REQUEST["back"])){
			$this->getCart()->setAttribute("page", "Cart04");
			soyshop_redirect_cart();
			exit;
		}

		if(isset($_GET["select_card"])){
			//Amazonログイン 出力
			SOY2::import("module.plugins.payment_amazon_pay.option.AmazonPayCardSelectPage");
			$form = SOY2HTMLFactory::createInstance("AmazonPayCardSelectPage");
		}else{
			//Amazonログイン 出力
			SOY2::import("module.plugins.payment_amazon_pay.option.AmazonPayLoginPage");
			$form = SOY2HTMLFactory::createInstance("AmazonPayLoginPage");
		}
		$form->execute();
		echo $form->getObject();
	}

	function onPostOptionPage(){
		//支払いのアクションを実行
		if(isset($_POST["orderReferenceId"]) && strlen($_POST["orderReferenceId"])){
			self::orderComplete();
		}
	}

	//支払い
	private function orderComplete(){
		$cart = $this->getCart();

		$order = soyshop_get_order_object($cart->getAttribute("order_id"));

		list($orderReferenceId, $amazonAuthorizationId) = SOY2Logic::createInstance("module.plugins.payment_amazon_pay.logic.AmazonPayLogic")->pay($order);
		if(is_null($amazonAuthorizationId)){	//エラーの場合
			throw new Exception("Amazon Pay Failed.");
		}

		//支払を完了する
		$order->setAttribute("payment_amazon_pay.order_reference_id", array(
			"name" => "Amazon注文リファレンスID",
			"value" => $orderReferenceId,
			"readonly" => true,
			"hidden" => true,
		));

		$order->setAttribute("payment_amazon_pay.amazon_authorization_id", array(
			"name" => "AmazonオーソリID",
			"value" => $amazonAuthorizationId,
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
}

SOYShopPlugin::extension("soyshop.payment",			"payment_amazon_pay", "AmazonPayPayment");
SOYShopPlugin::extension("soyshop.payment.option",	"payment_amazon_pay", "AmazonPayPayment");
