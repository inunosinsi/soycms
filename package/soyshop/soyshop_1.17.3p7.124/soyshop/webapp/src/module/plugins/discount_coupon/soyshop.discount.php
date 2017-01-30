<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once(dirname(__FILE__) . "/common.php");
include_once(dirname(__FILE__) . "/classes.php");

class SOYShopDiscountCouponModule extends SOYShopDiscount{
	private $config;
	
	function SOYShopDiscountCouponModule(){
		$this->config = SOYShopCouponUtil::getConfig();
	}

	function doPost($param){
		$cart = $this->getCart();

		$couponCodes = SOYShopCouponUtil::clean($param["coupon_codes"]);
		
		$module = new SOYShop_ItemModule();
		$module->setId("discount_coupon");
		$module->setName("クーポン");
		$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
		
		//割引金額：商品合計より大きくはならない。
		$discount = SOYShopCouponUtil::getDiscount($couponCodes);
		$discount = min($discount, $cart->getItemPrice());
		$module->setPrice(0 - $discount);//負の値
		
		if($discount > 0){
			$cart->addModule($module);
		}else{
			$cart->removeModule("discount_coupon");
		}

		//属性の登録
		$cart->setAttribute("discount_coupon.codes",$couponCodes);

		$cart->setOrderAttribute("discount_coupon.code","クーポンコード",implode(", ",$couponCodes));

	}
	
	function order(){
		$cart = $this->getCart();

		$codes = $cart->getAttribute("discount_coupon.codes");
		$orderId = $cart->getAttribute("order_id");
		$userId = $cart->getCustomerInformation()->getId();

		SOYShopCouponUtil::useCoupons($codes,$orderId,$userId);
	}

	function hasError($param){
		$cart = $this->getCart();

		$error = "";
		if(!isset($param["coupon_codes"]) OR !is_array($param["coupon_codes"]) OR count($param["coupon_codes"]) == 0){
			//$error = "クーポンコードを入力してください。";
		}elseif(!$this->checkItemPrice()){
			$error = "商品合計金額がクーポンの利用範囲外のためクーポンは使えません。";
		}else{
			$couponCodes = SOYShopCouponUtil::clean($param["coupon_codes"]);
			
			if(count($couponCodes) > $this->config->getAcceptNumber()){
				$error = "一度に利用可能なクーポンコードは" . $this->config->getAcceptNumber() . "つまでです。";
			}
			foreach($couponCodes as $code){
				if(!SOYShopCouponUtil::checkCode($code)){
					$error = "クーポンコードが無効です。";
					break;
				}
			}
			
			$cart->setAttribute("discount_coupon.codes",$couponCodes);
		}

		if(strlen($error) > 0){
			$cart->setAttribute("discount_coupon.error",$error);
			return true;
		}else{
			$cart->clearAttribute("discount_coupon.error");
			return false;
		}
	}
	
	function getError(){
		$cart = $this->getCart();
		return $cart->getAttribute("discount_coupon.error");
	}

	function getName(){
		if($this->checkItemPrice()){
			return "クーポン";
		}else{
			//使用可能金額の範囲外ならこのモジュールは表示しない
			return "";
		}
	}
	
	/**
	 * 使用可能金額の範囲内におさまっているかどうかを返す
	 * @return Boolean
	 */
	function checkItemPrice(){
		$cart = $this->getCart();

		$min = $this->config->getEnableAmountMin();
		$max = $this->config->getEnableAmountMax();
		
		$itemPrice = $cart->getItemPrice();
		
		if(
			strlen($min)>0 && is_numeric($min) && $itemPrice < $min
			OR
			strlen($max)>0 && is_numeric($max) && $max < $itemPrice
		){
			return false;
		}else{
			return true;
		}
		
	}

	function getDescription(){
		$cart = $this->getCart();
		
		$acceptNumber = $this->config->getAcceptNumber();
		$couponCodes  = $cart->getAttribute("discount_coupon.codes");

		$html = array();

//		$html[] = "クーポンコードがあれば入力してください。";
		$html[] = "<table><tr><th>クーポンコードがあれば入力してください。</th><td>";
		for($i=0;$i<$acceptNumber;$i++){
			$html[] = '<input type="text" size="40" name="discount_module[discount_coupon][coupon_codes][]" value="'.htmlspecialchars(( isset($couponCodes[$i]) ? $couponCodes[$i] : "" ), ENT_QUOTES, "UTF-8").'" />';
			$html[]='<br/>';
		}
		$html[] = '</td></table>';

		return implode("",$html);
	}


}
SOYShopPlugin::extension("soyshop.discount","discount_coupon","SOYShopDiscountCouponModule");
?>