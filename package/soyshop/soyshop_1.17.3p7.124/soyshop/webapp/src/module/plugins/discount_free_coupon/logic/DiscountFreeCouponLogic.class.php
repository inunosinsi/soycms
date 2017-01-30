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
	
	private $couponDao;
	private $coupon;
	
	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
	}
	
	/**
	 * @param string code, integer userId
	 * @return boolean
	 */
	function checkUsable($code, $userId){
		
		//クーポンが存在するかチェック
		if($this->checkCouponExists($code)){
			//チェックする関数内で取得したものを取り出す
			$coupon = $this->coupon;
		}else{
			return false;
		}
		
		//有効期限のチェック
		if(!$this->checkTimeLimit($coupon)){
			return false;
		}
				
		//ユーザIDがnullの場合は初めての購入になるので、購入履歴のチェックはスルーする
		if(isset($userId)){
			//ヒストリーテーブルのチェック
			if(!$this->checkHistory($coupon, $userId)){
				return false;
			}
		}

		return true;
	}
	
	function checkEachCouponUsable($code, $itemPrice){
		
		if(!$this->coupon){
			$this->coupon = $this->getCoupon($code);
		}
		
		$coupon = $this->coupon;
		
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
		
		$coupon = $this->getCoupon($code);
		
		return (!is_null($coupon->getId())) ? true : false;
	}
	
	/**
	 * @param object SOYShop_Coupon
	 * @return boolean
	 */
	function checkTimeLimit(SOYShop_Coupon $coupon){
		$timeLimitStart = $coupon->getTimeLimitStart();
		$timeLimitEnd = $coupon->getTimeLimitEnd();
		return ($timeLimitStart < time() && $timeLimitEnd > time()) ? true : false;
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
		return (count($result) < $coupon->getCount()) ? true : false;
	}
		
	function getCoupon($code){
		if(!$this->couponDao){
			$this->couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		}
		
		$dao = $this->couponDao;
		
		try{
			$coupon = $dao->getByCouponCodeAndNoDelete($code);
		}catch(Exception $e){
			$coupon = new SOYShop_Coupon();
		}
		
		$this->coupon = $coupon;
		
		return $coupon;
	}
}
?>