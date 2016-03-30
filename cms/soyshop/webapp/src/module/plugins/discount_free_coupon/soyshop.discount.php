<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYShopDiscountFreeCouponModule extends SOYShopDiscount{
	private $config;
	private $dao;
	
	function SOYShopDiscountFreeCouponModule(){
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::imports("module.plugins.discount_free_coupon.util.*");
		$this->dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$util = new DiscountFreeCouponUtil();
		$this->config = $util->getConfig();
	}
	
	function clear(){
		$cart = $this->getCart();
		
		$cart->removeModule("discount_free_coupon");
		$cart->clearAttribute("discount_free_coupon.code");
		$cart->clearOrderAttribute("discount_free_coupon.code");
	}

	function doPost($param){
		$cart = $this->getCart();
		$code = trim($param["coupon_codes"][0]);		
		
		if(isset($code)){
			try{
				$coupon = $this->dao->getByCouponCodeAndNoDelete($code);
			}catch(Exception $e){
				$coupon = new SOYShop_Coupon();
			}
			
			$couponId = $coupon->getId();
			
			if(isset($couponId)){
				$module = new SOYShop_ItemModule();
				$module->setId("discount_free_coupon");
				$module->setName(MessageManager::get("MODULE_NAME_COUPON"));
				$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
				
				//クーポンのタイプにより、割引額を変える
				$couponType = $coupon->getCouponType();
				//値引き額
				if($couponType == SOYShop_Coupon::TYPE_PRICE){
					$discount = $coupon->getDiscount();
					//割引金額：商品合計より大きくはならない。
					$discount = min($discount, $cart->getItemPrice());
					
				//値引き率
				}elseif($couponType == SOYShop_Coupon::TYPE_PERCENT){
					$discount = $cart->getItemPrice() * $coupon->getDiscountPercent() / 100;
				
				//念のため
				}else{
					$discount = 0;
				}
				
				$module->setPrice(0 - $discount);//負の値
				
				if($discount > 0){
					$cart->addModule($module);
					
					//属性の登録
					$cart->setAttribute("discount_free_coupon.code", $code);
					$cart->setOrderAttribute("discount_free_coupon.code", MessageManager::get("MODULE_NAME_COUPON"), $code);
				}
			}	
		}
	}
	
	function order(){
		//処理はorder.completeで行う
	}

	/**
	 * クーポンが使用可能か？調べる
	 */
	function hasError($param){
		
		$cart = $this->getCart();
		$error = "";
		
		//クーポンが入力されなかった場合は何もしない
		if(isset($param["coupon_codes"][0]) && strlen($param["coupon_codes"][0]) > 0){
			$code = trim($param["coupon_codes"][0]);
			$userId = $cart->getAttribute("logined_userid");
			
			//ユーザIDが取得できなかった場合、念の為、ユーザテーブルからオブジェクトを取得
			if(is_null($userId)){
				$userId = $this->getUserId($cart);
			}

			if(!isset($param["coupon_codes"]) || !is_array($param["coupon_codes"]) || count($param["coupon_codes"]) === 0){
				//$error = "クーポンコードを入力してください。";
			}elseif(!$this->checkItemPrice()){
				$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
			}else{
				$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic");
				if(!$logic->checkUsable($code, (int)$userId)){
					$error = MessageManager::get("INVALID_COUPON_CODE");
				}elseif(!$logic->checkEachCouponUsable($code, (int)$cart->getItemPrice())){
					$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
				}else{
					//
				}
			}
			
			$cart->setAttribute("discount_free_coupon.code", $code);
		}
		
		if(strlen($error) > 0){
			$cart->setAttribute("discount_free_coupon.error", $error);
			return true;
		}else{
			$cart->clearAttribute("discount_free_coupon.error");
			return false;
		}
	}
	
	function getError(){
		$cart = $this->getCart();
		return $cart->getAttribute("discount_free_coupon.error");
	}

	function getName(){
		if($this->checkItemPrice()){
			return MessageManager::get("MODULE_NAME_COUPON");
		}else{
			//使用可能金額の範囲外ならこのモジュールは表示しない
			return "";
		}
	}
	
	function getUserId($cart){
		$user = $cart->getCustomerInformation();	//userIdが存在していない状態
			
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		//userIdを取得する
		try{
			$user = $userDao->getByMailAddress($user->getMailAddress());
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
			
		return $user->getId();
	}
	
	/**
	 * 使用可能金額の範囲内におさまっているかどうかを返す
	 * @return Boolean
	 */
	function checkItemPrice(){
		$cart = $this->getCart();
		
		$config = $this->config;

		$min = $config["min"];
		$max = $config["max"];
		
		//$maxが空だった場合、限りなく大きな数字を代入しておく
		if(strlen($max) === 0) $max = 1000000000;
		
		$itemPrice = $cart->getItemPrice();
		
		if(
			strlen($min) > 0 && is_numeric($min) && $itemPrice < $min
			||
			strlen($max) > 0 && is_numeric($max) && $max < $itemPrice
		){
			return false;
		}else{
			return true;
		}
	}

	function getDescription(){
		$cart = $this->getCart();
		
		$code  = $cart->getAttribute("discount_free_coupon.code");
		$html = array();
		
		$html[] = "<table><tr><th>" . MessageManager::get("INPUT_COUPON_CODE") . "</th><td>";
		$html[] = '<input type="text" size="40" name="discount_module[discount_free_coupon][coupon_codes][]" value="'.htmlspecialchars($code, ENT_QUOTES, "UTF-8").'" />';
		$html[]='<br/>';
		$html[] = '</td></table>';

		return implode("",$html);
	}
}
SOYShopPlugin::extension("soyshop.discount", "discount_free_coupon", "SOYShopDiscountFreeCouponModule");
?>