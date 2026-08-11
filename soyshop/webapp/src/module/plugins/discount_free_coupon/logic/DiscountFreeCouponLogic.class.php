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
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponDAO");
	}

	/**
	 * 使用可能金額の範囲内におさまっているかどうかを返す
	 * @return Boolean
	 */
	function checkItemPrice(int $total){
		static $res;
		if(is_null($res)){
			SOY2::import("module.plugins.discount_free_coupon.util.DiscountFreeCouponUtil");
			$cnf = DiscountFreeCouponUtil::getConfig();

	  		$min = (isset($cnf["min"]) && strlen($cnf["min"]) && is_numeric($cnf["min"])) ? (int)$cnf["min"] : 0;
	 		$max = (isset($cnf["max"]) && strlen($cnf["max"]) && is_numeric($cnf["max"])) ? (int)$cnf["max"] : 1000000000; //$maxが空だった場合、限りなく大きな数字を代入しておく

	  		$res = ($total > $min || $total < $max);
		}
		return $res;
  	}

	/**
	 * @param string code, integer userId
	 * @return boolean
	 */
	function checkUsable(string $code, int $userId){

		//クーポンが存在するかチェック
		if(!self::_checkCouponExists($code)) return false;

		//有効期限のチェック
		$coupon = self::_getCouponByCode($code); //チェックする関数内で取得したものを取り出す
		if(!self::_checkTimeLimit($coupon)) return false;

		//ユーザIDが0の場合は初めての購入になるので、購入履歴のチェックはスルーする
		if($userId === 0) return true;

		//二回目移行の場合は履歴を確認する
		return (self::_checkHistory($coupon, $userId));
	}

	function checkEachCouponUsable(string $code, int $itemPrice){

		$coupon = self::_getCouponByCode($code);

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
	private function _checkCouponExists(string $code){
		$coupon = self::_getCouponByCode($code);
		return (is_numeric($coupon->getId()) && $coupon->getId() > 0);
	}

	/**
	 * @param object SOYShop_Coupon
	 * @return boolean
	 */
	private function _checkTimeLimit(SOYShop_Coupon $coupon){
		return ($coupon->getTimeLimitStart() < time() && $coupon->getTimeLimitEnd() > time());
	}

	/**
	 * @param object SOYShop_Coupon coupon, integer userId
	 * @return boolean
	 */
	private function _checkHistory(SOYShop_Coupon $coupon, int $userId){
		$dao = new SOY2DAO();

		$sql = "SELECT * ".
				"FROM soyshop_coupon_history ".
				"WHERE user_id = :userId AND ".
						"coupon_id = :couponId;";

		try{
			$res = $dao->executeQuery($sql, array(":userId" => $userId, ":couponId" => $coupon->getId()));
		}catch(Exception $e){
			return true;
		}

		//使用回数のチェック
		return (count($res) < $coupon->getCount());
	}

	function getDiscountPriceByCode(string $code){
		return self::_getDiscountPrice($code, $this->cart->getItemPrice());
	}

	function getDiscountPriceByCodeWithTotalPrice(string $code, int $total){
		return self::_getDiscountPrice($code, $total);
	}

	private function _getDiscountPrice(string $code, int $total){
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

	function getCouponByCode(string $code){
		return self::_getCouponByCode($code);
	}

	private function _getCouponByCode(string $code){
		static $coupons, $dao;	//複数個のクーポンコードに対応できるように
		if(!is_array($coupons)) {
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

	function getCouponNameListByIds(array $ids){
		if(!count($ids)) return array();
		return SOY2DAOFactory::create("SOYShop_CouponDAO")->getByIds($ids);
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
