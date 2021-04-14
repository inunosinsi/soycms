<?php

/**
 *
 * discount.coupon.config: SOYShopCouponConfig
 * discount.coupon.list: Array(id => SOYShopCoupon)
 * SOYShopCoupon.couponCodes: Array(code => SOYShopCouponCode)
 * discount.coupon.code.XXX: SOYShopCouponCode
 *
 *
 */

class DiscountFreeCouponLogic extends SOY2LogicBase{

	private $cart;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::import("module.plugins.discount_free_coupon.util.DiscountFreeCouponUtil");
	}

	/**
	 * 使用可能金額の範囲内におさまっているかどうかを返す
	 * @return Boolean
	 */
	function checkItemPrice($total){
		static $res;
		if(is_null($res)){
			$config = DiscountFreeCouponUtil::getConfig();

	  		$min = (isset($config["min"]) && strlen($config["min"]) && is_numeric($config["min"])) ? (int)$config["min"] : 0;
	 		$max = (isset($config["max"]) && strlen($config["max"]) && is_numeric($config["max"])) ? (int)$config["max"] : 1000000000; //$maxが空だった場合、限りなく大きな数字を代入しておく

	  		$res = ($total > $min || $total < $max);
		}
		return $res;
  	}

	/**
	 * @param string code, integer userId
	 * @return boolean
	 */
	function checkUsable($code, $userId){

		//クーポンが存在するかチェック
		if(!$this->checkCouponExists($code)) return false;

		//有効期限のチェック
		$coupon = self::getCouponByCode($code); //チェックする関数内で取得したものを取り出す
		if(!self::checkTimeLimit($coupon)) return false;

		//ユーザIDがnullの場合は初めての購入になるので、購入履歴のチェックはスルーする
		if(isset($userId) && !$this->checkHistory($coupon, $userId)) return false;

		return true;
	}

	function checkEachCouponUsable($code, $itemPrice){

		$coupon = self::getCouponByCode($code);

		//各クーポンの利用価格チェック
		$priceLimitMin = $coupon->getPriceLimitMin();

		//商品の合計が利用可能価格下限よりも少ない場合はエラー
		if(!is_null($priceLimitMin) && (int)$priceLimitMin > 0){
			if((int)$priceLimitMin > $itemPrice){
				return false;
			}
		}

		$priceLimitMax = $coupon->getPriceLimitMax();

		//商品の合計が利用可能価格上限よりも多い場合はエラー
		if(!is_null($priceLimitMax) && (int)$priceLimitMax > 0){
			if((int)$priceLimitMax < $itemPrice){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string code
	 * @return boolean
	 */
	function checkCouponExists($code){
		return (!is_null(self::getCouponByCode($code)->getId()));
	}

	/**
	 * @param object SOYShop_Coupon
	 * @return boolean
	 */
	function checkTimeLimit(SOYShop_Coupon $coupon){
		$timeLimitStart = $coupon->getTimeLimitStart();
		$timeLimitEnd = $coupon->getTimeLimitEnd();
		return ($timeLimitStart < time() && $timeLimitEnd > time());
	}

	/**
	 * @param object SOYShop_Coupon coupon, integer userId
	 * @return boolean
	 */
	function checkHistory(SOYShop_Coupon $coupon, $userId){
		$couponId = $coupon->getId();

		$dao = new SOY2DAO();

		$sql = "SELECT * ".
				"FROM soyshop_coupon_history ".
				"WHERE user_id = :userId AND ".
						"coupon_id = :couponId;";

		try{
			$result = $dao->executeQuery($sql, array(":userId"=>$userId, ":couponId"=>$couponId));
		}catch(Exception $e){
			return true;
		}

		//使用回数のチェック
		return (count($result) < $coupon->getCount());
	}

	function getDiscountPriceByCode($code){
		return self::_getDiscountPrice($code, $this->cart->getItemPrice());
	}

	function getDiscountPriceByCodeWithTotalPrice($code, $total){
		return self::_getDiscountPrice($code, $total);
	}

	private function _getDiscountPrice($code, $total){
		$coupon = self::getCouponByCode(trim($code));
		if(is_null($coupon->getId())) return 0;

		//クーポンのタイプにより、割引額を変える
		switch($coupon->getCouponType()){
			//値引き額
			case SOYShop_Coupon::TYPE_PRICE:
				//割引金額：商品合計より大きくはならない。
				return min($coupon->getDiscount(), $total);
			//値引き率
			case SOYShop_Coupon::TYPE_PERCENT:
				return (int)($total * $coupon->getDiscountPercent() / 100);
			//送料無料
			case SOYShop_Coupon::TYPE_DELIVERY:
				//カートの時のみ使用可
				if(!is_null($this->cart)){
					foreach($this->cart->getModules() as $moduleId => $module){
						if(strpos($moduleId, "delivery") === 0){
							return $module->getPrice();
						}
					}
				}
		}
		return 0;
	}

	function getCouponByCode($code){
		static $coupons, $dao;	//複数個のクーポンコードに対応できるように
		if(is_null($coupons)) {
			$coupons = array();
			$dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		}

		if(!isset($coupons[$code])){
			try{
				$coupons[$code] = $dao->getByCouponCodeAndNoDelete($code);
			}catch(Exception $e){
				$coupons[$code] = new SOYShop_Coupon();
			}
		}

		return $coupons[$code];
	}

	function getCouponNameListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();
		return SOY2DAOFactory::create("SOYShop_CouponDAO")->getByIds($ids);
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
