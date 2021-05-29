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

		if(isset($_GET[AmazonPayUtil::REDIRECT_PARAM])){
			//Amazonログイン 出力
			SOY2::import("module.plugins.payment_amazon_pay.option.cart05.AmazonPayCardSelectPage");
			$form = SOY2HTMLFactory::createInstance("AmazonPayCardSelectPage");
		}else{
			//Amazonログイン 出力
			SOY2::import("module.plugins.payment_amazon_pay.option.cart05.AmazonPayLoginPage");
			$form = SOY2HTMLFactory::createInstance("AmazonPayLoginPage");
		}
		$form->execute();
		echo $form->getObject();
	}

	function onPostOptionPage(){
		//エラーがある場合
		if(isset($_POST["amazonPayErrorMessage"]) && strlen($_POST["amazonPayErrorMessage"])){
			//getOptionPageの方に処理を続けるようにここでは何もしない
		//支払いのアクションを実行
		}else if(isset($_POST["orderReferenceId"]) && strlen($_POST["orderReferenceId"])){
			self::orderComplete();
		}
	}

	//支払い
	private function orderComplete(){
		$cart = $this->getCart();

		$order = soyshop_get_order_object($cart->getAttribute("order_id"));

		list($orderReferenceId, $amazonAuthorizationId, $err) = SOY2Logic::createInstance("module.plugins.payment_amazon_pay.logic.AmazonPayLogic")->pay($order);
		if(isset($err) && strlen($err)){	//エラーの場合
			$cart->addErrorMessage("amazon_pay_error", $err);
			$cart->save();
			soyshop_redirect_cart("select_card");
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

		//支払いステータスの変更。
		$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);

		// CompleteページにorderComplete()があるが、エラーにならずに回避してくれるのでここでorderComplete()を実行しておく
		$cart->orderComplete();

		$cart->setAttribute("page", "Complete");
		soyshop_redirect_cart();
		exit;
	}
}

SOYShopPlugin::extension("soyshop.payment",			"payment_amazon_pay", "AmazonPayPayment");
SOYShopPlugin::extension("soyshop.payment.option",	"payment_amazon_pay", "AmazonPayPayment");
