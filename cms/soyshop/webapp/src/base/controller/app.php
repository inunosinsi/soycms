<?php

/**
 * カートやマイページの処理を行う
 * @param String $uri
 * @param Array $args
 * @return Boolean
 */
function do_application($uri, $args){
	//非同期でカートもしくはマイページの状況を返す
	if(isset($args[0]) && $args[0] == "async"){
		generate_application_page_situation_json();
	}

	//カート マイページ 共通化
	SOY2::imports("base.site.classes.*");
	SOY2::import("base.site.SOYShopPageBase");

	SOY2::import("component.backward.BackwardUserComponent");
	SOY2::import("component.UserComponent");

	//カートの多言語化
	SOY2::import("message.MessageManager");

	//カート
	if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
		include_once("cart.php");

		MessageManager::addMessagePath("cart");

		//notify event
		if(isset($_GET["soyshop_notification"])){
			execute_notification_action($_GET["soyshop_notification"]);
			exit;
		}

		//block event
		if(isset($_GET["soyshop_ban"])){
			execute_ban_action($_GET["soyshop_ban"]);
			exit;
		}

		execute_cart_application($args);
		return true;
	}

	//マイページ
	if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE){
		include_once("mypage.php");

		MessageManager::addMessagePath("mypage");

		//代理購入の為のログイン
		purchase_proxy_login();

		//download_event
		if(isset($_GET["soyshop_download"])){
			execute_download_action($_GET["soyshop_download"]);
			exit;
		}

		//上のdownload_eventのGETパラメータのキーだと紛らわしいので別のキーを用意
		if(isset($_GET["soyshop_action"])){
			execute_download_action($_GET["soyshop_action"]);
			exit;
		}

		execute_mypage_application($args);
		return true;
	}
}

//非同期でカートもしくはマイページの状況を取得する
function generate_application_page_situation_json(){
	if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
		$cart = CartLogic::getCart();
		$array = array(
			"count" => $cart->getOrderItemCount(),
			"total" => $cart->getItemPrice()
		);
		echo json_encode($array);
	}

	if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE){
		$mypage = MyPageLogic::getMyPage();
		$isLoggedIn = ($mypage->getIsLoggedIn()) ? 1 : 0;

		$array = array(
			"login" => $isLoggedIn,
			"name" => ($isLoggedIn) ? $mypage->getUser()->getName() : "",
			"point" => ($isLoggedIn) ? $mypage->getUser()->getPoint() : 0
		);
		echo json_encode($array);
	}

	exit;
}
