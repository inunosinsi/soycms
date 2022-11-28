<?php

class DiscountFreeCouponCsvLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
	}

	function getLabels(){

		$labels = array();

		$labels[] = "ID";
		$labels[] = "クーポンコード";
		$labels[] = "クーポンの設定";
		$labels[] = "クーポン名";
		$labels[] = "回数";

		$labels[] = "使用数";
		$labels[] = "値引き額";
		$labels[] = "値引き率";
		$labels[] = "利用可能金額下限";
		$labels[] = "利用可能金額上限";

		$labels[] = "有効期限開始日";
		$labels[] = "有効期限終了日";
		$labels[] = "状態";
		$labels[] = "備考";
		$labels[] = "作成日";

		return $labels;
	}

	function getLines(){

		$lines = array();

		$couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$couponHistoryDao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");

		try{
			$coupons = $couponDao->getByIsDelete(SOYShop_Coupon::NOT_DELETED);
		}catch(Exception $e){
			return array();
		}

		foreach($coupons as $coupon){
			$line = array();

			$line[] = $coupon->getId();
			$line[] = $coupon->getCouponCode();
			switch($coupon->getCouponType()){
				case SOYShop_Coupon::TYPE_PRICE:
					$line[] = "値引き額";
					break;
				case SOYShop_Coupon::TYPE_PERCENT:
					$line[] = "値引き率";
					break;
				case SOYShop_Coupon::TYPE_DELIVERY:
					$line[] = "送料無料";
					break;
			}
			$line[] = $coupon->getName();
			$line[] = ($coupon->getCount() > 900000) ? "無制限" : $coupon->getCount();

			try{
				$use = $couponHistoryDao->countByCouponId($coupon->getId());
			}catch(Exception $e){
				$use = 0;
			}
			$line[] = $use;

			$line[] = ((int)$coupon->getDiscount() > 0) ? $coupon->getDiscount() . "円" : "";
			$line[] = ((int)$coupon->getDiscountPercent() > 0) ? $coupon->getDiscountPercent() . "%" : "";
			$line[] = ((int)$coupon->getPriceLimitMin() > 0) ? $coupon->getPriceLimitMin() . "円" : "";
			$line[] = ((int)$coupon->getPriceLimitMax() > 0) ? $coupon->getPriceLimitMax() . "円" : "";
			$line[] = date("Y-m-d", $coupon->getTimeLimitStart());
			$line[] = date("Y-m-d", $coupon->getTimeLimitEnd());
			$line[] = ($coupon->getIsDelete() == 0) ? "公開" : "削除";
			$line[] = $coupon->getMemo();

			$line[] = date("Y-m-d", $coupon->getCreateDate());

			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	function getLogLabels(){

		$labels = array();

		$labels[] = "クーポンコード";
		$labels[] = "クーポン名";
		$labels[] = "顧客";
		$labels[] = "購入日";
		$labels[] = "購入時刻";
		$labels[] = "購入金額(送料込み)";
		$labels[] = "値引き額";
		$labels[] = "値引き後金額";

		return $labels;
	}

	function getLogLines(){

		set_time_limit(0);

		//取得するログの期間を設定する
		if(isset($_POST["csv_date_start"]) && strlen($_POST["csv_date_start"]) > 0){
			$start = DiscountFreeCouponUtil::convertDate($_POST["csv_date_start"]);
		}else{
			$start = DiscountFreeCouponUtil::DATE_START;
		}

		if(isset($_POST["csv_date_end"]) && strlen($_POST["csv_date_end"]) > 0){
			$end = DiscountFreeCouponUtil::convertDate($_POST["csv_date_end"]) + 24*60*60;	//翌日分を加算
		}else{
			$end = DiscountFreeCouponUtil::DATE_END;
		}

		SOY2::import("domain.order.SOYShop_ItemModule");

		$couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$couponHistoryDao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			$histories = $couponHistoryDao->getByDate($start, $end);
		}catch(Exception $e){
			return array();
		}

		$lines = array();
		foreach($histories as $history){
			$line = array();

			try{
				$order = $orderDao->getById($history->getOrderId());
			}catch(Exception $e){
				continue;
			}

			//注文がキャンセルの場合はスルーする。
			if($order->getStatus() == SOYShop_Order::ORDER_STATUS_CANCELED) continue;

			//1.11.5以降は履歴に値引き額が格納されている
			if($history->getPrice() > 0){
				$couponPrice = $history->getPrice();

			//1.11.4以前対策。注文のオブジェクトからクーポンの値引き額を調べる
			}else{
				//支払い履歴がない場合もスルーする。
				$modules = $order->getModuleList();
				if(!isset($modules["discount_free_coupon"])) continue;
				$couponValues = $modules["discount_free_coupon"];

				$couponPrice = abs($couponValues->getPrice());
			}


			try{
				$coupon = $couponDao->getById($history->getCouponId());
			}catch(Exception $e){
				$coupon = new SOYShop_Coupon();
			}

			$line[] = $coupon->getCouponCode();
			$line[] = $coupon->getName();

			try{
				$user = $userDao->getById($history->getUserId());
			}catch(Exception $e){
				continue;
			}

			$line[] = $user->getName();
			$line[] = date("Y/m/d", $history->getCreateDate());
			$line[] = date("H:i:s", $history->getCreateDate());

			$line[] = (int)$order->getPrice() + $couponPrice;	//購入価格
			$line[] = $couponPrice;		//値引き価格
			$line[] = (int)$order->getPrice();


			$lines[] = implode(",", $line);
		}

		return $lines;
	}
}
