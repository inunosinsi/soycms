<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYShopDiscountFreeCouponModule extends SOYShopDiscount{

	private $dao;

	function __construct(){
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::imports("module.plugins.discount_free_coupon.util.*");
		$this->dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
	}

	function clear(){
		$cart = $this->getCart();

		$cart->removeModule("discount_free_coupon");
		$cart->clearAttribute("discount_free_coupon.code");
		$cart->clearOrderAttribute("discount_free_coupon.code");
	}

	function doPost($param){
		$cart = $this->getCart();
		$code = (isset($param["coupon_codes"][0])) ? trim($param["coupon_codes"][0]) : null;

		if(strlen($code)){
			$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic", array("cart" => $cart));
			$discount = $logic->getDiscountPriceByCode($code);

			if($discount > 0){
				$module = new SOYShop_ItemModule();
				$module->setId("discount_free_coupon");
				$module->setName(MessageManager::get("MODULE_NAME_COUPON"));
				$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
				$module->setPrice(0 - $discount);//負の値

				$cart->addModule($module);

				//属性の登録
				$cart->setAttribute("discount_free_coupon.code", $code);
				$cart->setOrderAttribute("discount_free_coupon.code", MessageManager::get("MODULE_NAME_COUPON"), $code);
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

			//ユーザIDが取得できなかった場合、念の為、ユーザテーブルからオブジェクトを取得
			$userId = $cart->getAttribute("logined_userid");
			if(is_null($userId)) $userId = self::getUserIdByMailAddress($cart->getCustomerInformation()->getMailAddress());

			$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic");
			if(!isset($param["coupon_codes"]) || !is_array($param["coupon_codes"]) || count($param["coupon_codes"]) === 0){
				//$error = "クーポンコードを入力してください。";
			}elseif(!$logic->checkItemPrice($this->getCart()->getItemPrice())){
				$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
			}else{
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
		return $this->getCart()->getAttribute("discount_free_coupon.error");
	}

	function getName(){
		if(SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic")->checkItemPrice($this->getCart()->getItemPrice())){
			return MessageManager::get("MODULE_NAME_COUPON");
		}else{
			//使用可能金額の範囲外ならこのモジュールは表示しない
			return "";
		}
	}

	private function getUserIdByMailAddress($mailAddress){
		//userIdを取得する
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailAddress)->getId();
		}catch(Exception $e){
			return null;
		}
	}

	function getDescription(){
		$code  = $this->getCart()->getAttribute("discount_free_coupon.code");

		$html = array();
		$html[] = "<table><tr><th>" . MessageManager::get("INPUT_COUPON_CODE") . "</th><td>";
		$html[] = '<input type="text" size="40" name="discount_module[discount_free_coupon][coupon_codes][]" value="'.htmlspecialchars($code, ENT_QUOTES, "UTF-8").'" />';
		$html[]='<br/>';
		$html[] = '</td></table>';

		return implode("", $html);
	}
}
SOYShopPlugin::extension("soyshop.discount", "discount_free_coupon", "SOYShopDiscountFreeCouponModule");
