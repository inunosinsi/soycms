<?php

class PaymentAmazonPayCart extends SOYShopCartBase{

	function __construct(){

	}

	function doOperation(){}
	function afterOperation(CartLogic $cart){}

	function displayUpperParts02(CartLogic $cart){
		return null;
		// $mypage = MyPageLogic::getMyPage();
		// $userId = $mypage->getUserId();
		// if(!is_numeric($userId)) return null;
		//
		// //メールアドレス以外のデータが格納されている場合も表示しない
		// $user = soyshop_get_user_object($userId);
		// if(strlen($user->getZipCode())) return null;
		//
		// //amazonIDがあるか確認
		// // $amazonId = SOY2Logic::createInstance("module.plugins.login_with_amazon.logic.LoginWithAmazonLogic")->getAmazonIdByUserId($user->getId());
		// // if(!strlen($amazonId)) return null;
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
