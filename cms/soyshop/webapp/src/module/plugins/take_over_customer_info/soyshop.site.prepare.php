<?php

class TakeOverCustomerInfoPrepare extends SOYShopSitePrepareAction{

	function prepare(){

		//ctk={tracking_number}-{md5(order_id+user_id_order_date)}を受け取ったら、指定のサイトから顧客情報を取得する。ctkはcustomer tokenの略
		if(!isset($_GET["ctk"])) return;
		$ctk = $_GET["ctk"];

		//debug用
		//$token = md5(5 + 1 + 1594330957);	//156e4de1b14afb506760c89944b27da6
		//$ctk = "1-2012-3800-156e4de1b14afb506760c89944b27da6";

		$v = explode("-", $ctk);
		for($i = 0; $i < 3; $i++){
			if(!isset($v[$i]) || !is_numeric($v[$i])) return;
		}
		if(!isset($v[3])) return;

		//別サイトのDSNを取得する
		$dsn = self::_getDsn();
		if(!strlen($dsn)) return;

		$trackingNumber = $v[0] . "-" . $v[1] . "-" . $v[2];
		$token = $v[3];

		$oldDsn = SOY2DAOConfig::Dsn();
		SOY2DAOConfig::Dsn($dsn);

		try{
			$order = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getByTrackingNumber($trackingNumber);
		}catch(Exception $e){
			SOY2DAOConfig::Dsn($oldDsn);
			return;
		}

		//tokenが正しいか確認する
		if(md5($order->getId() + $order->getUserId() + $order->getOrderDate()) != $token) {
			SOY2DAOConfig::Dsn($oldDsn);
			return;
		}
		$user = soyshop_get_user_object($order->getUserId());
		SOY2DAOConfig::Dsn($oldDsn);

		if(is_null($user->getId())) return;
		$user->setId(null);	//当ショップの顧客ではないため、IDをnullにする

		//取得したメールアドレスで検索をする
		try{
			$tmp = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($user->getMailAddress());
			if(is_numeric($tmp->getId())){
				$user = $tmp;
				unset($tmp);
			}
		}catch(Exception $e){
			//
		}

		//カートに顧客情報を登録する
		$cart = CartLogic::getCart();
		$cart->setCustomerInformation($user);
		$cart->setAttribute("logined", true);
		if(is_numeric($user->getId())) $cart->setAttribute("logined_userid", $user->getId());
		$cart->save();

		// マイページの方でログイン済みにしておく
		// if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());
		// $mypage = MyPageLogic::getMyPage();
		// $mypage->setAttribute("loggedin", true);
		// if(is_numeric($user->getId())) $mypage->setAttribute("userId", $user->getId());
		// $mypage->save();
	}

	private function _getDsn(){
		SOY2::import("module.plugins.take_over_customer_info.util.TakeOverCustomerUtil");
		$cnf = TakeOverCustomerUtil::getConfig();

		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAdminDsn();
		try{
			$dsn = SOY2DAOFactory::create("admin.SiteDAO")->getById($cnf["shopId"])->getDataSourceName();
		}catch(Exception $e){
			$dsn = null;
		}
		SOYAppUtil::resetAdminDsn($old);
		return $dsn;
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "take_over_customer_info", "TakeOverCustomerInfoPrepare");
