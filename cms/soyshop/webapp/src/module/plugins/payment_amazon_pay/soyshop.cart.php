<?php

class PaymentAmazonPayCart extends SOYShopCartBase{

	function __construct(){

	}

	function doOperation(){}
	function afterOperation(CartLogic $cart){}

	function doPost02(CartLogic $cart){
		//情報を取り出す
		// if(isset($_POST["orderReferenceId"]) && strlen($_POST["orderReferenceId"])){
		// 	SOY2Logic::createInstance("module.plugins.payment_amazon_pay.logic.AmazonPayLogic")->address($_POST["orderReferenceId"]);
		// }
	}

	function displayUpperParts02(CartLogic $cart){
		//下記のコードはダメだったけれども、一応残す
		return null;

		//メールアドレス以外のデータが格納されている場合も表示しない
		// $user = $cart->getCustomerInformation();
		// if(strlen($user->getZipCode())) return null;
		//
		// //住所選択の画面を表示する
		// if(isset($_GET[AmazonPayUtil::REDIRECT_PARAM])){
		// 	SOY2::import("module.plugins.payment_amazon_pay.option.cart02.AmazonPayAddressPage");
		// 	$form = SOY2HTMLFactory::createInstance("AmazonPayAddressPage");
		// }else{
		// 	//Amazonログイン 出力
		// 	SOY2::import("module.plugins.payment_amazon_pay.option.cart02.AmazonPayLoginPage");
		// 	$form = SOY2HTMLFactory::createInstance("AmazonPayLoginPage");
		// }
		// $form->execute();
		// return $form->getObject();
	}

	function displayPage02(CartLogic $cart){}
	function displayPage04(CartLogic $cart){}
}
SOYShopPlugin::extension("soyshop.cart", "payment_amazon_pay", "PaymentAmazonPayCart");
