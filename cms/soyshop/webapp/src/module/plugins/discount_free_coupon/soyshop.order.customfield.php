<?php

class DiscountFreeCouponOrderCustomfieldModule extends SOYShopOrderCustomfield{

	private $config;

	function clear(CartLogic $cart){
		//管理画面側では使い勝手が悪いので、ここでセッションのクリアは行わない
	}

	function doPost($param){
		//管理画面からの注文の時のみ動作
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE && strpos($_SERVER["REQUEST_URI"], "/Order/Register")){

			$cart = $this->getCart();
			$cart->clearOrderAttribute("discount_free_coupon.code");
			$cart->clearOrderAttribute("discount_free_coupon.category");
			$cart->removeModule("discount_free_coupon");

			$param = (isset($param["discount_free_coupon"])) ? $param["discount_free_coupon"] : array();

			//先にカテゴリを取得
			if(isset($param["categoryId"]) && is_numeric($param["categoryId"])){
				$name = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.CouponCategoryLogic")->getCategoryNameById($param["categoryId"]);
				$cart->setOrderAttribute("discount_free_coupon.category", "カテゴリ", $name);
			}

			//コード
			$code = (isset($param["couponCode"])) ? trim($param["couponCode"]) : null;
			if(strlen($code)){
				$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic", array("cart" => $cart));
				$discount = $logic->getDiscountPriceByCode($code);

				if($discount){
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
	}

	function complete(CartLogic $cart){}

	function hasError($param){
		//管理画面からの注文の時のみ動作
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE && strpos($_SERVER["REQUEST_URI"], "/Order/Register")){
			$cart = $this->getCart();

			//クリア
			foreach(array("code", "category", "error") as $t){
				$cart->clearAttribute("discount_free_coupon." . $t);
			}

			//使いやすいように整形
			$param = (isset($param["discount_free_coupon"])) ? $param["discount_free_coupon"] : array();

			//カテゴリを選択している場合はここでセッションに入れておく
			if(isset($param["categoryId"])){
				$cart->setAttribute("discount_free_coupon.category", $param["categoryId"]);
			}

			$error = "";

			//クーポンコードが存在しているか？
			$code = (isset($param["couponCode"]) && strlen($param["couponCode"])) ? trim($param["couponCode"]) : null;
			if(isset($code)){
				$cart->setAttribute("discount_free_coupon.code", $code);	//最初にセッションに入れておく

				$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic");
				if(!$logic->checkItemPrice($cart->getItemPrice())){
					$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
				}else{
					if(!$logic->checkUsable($code, (int)$cart->getCustomerInformation()->getId())){
						$error = MessageManager::get("INVALID_COUPON_CODE");
					}elseif(!$logic->checkEachCouponUsable($code, (int)$cart->getItemPrice())){
						$error = MessageManager::get("NOT_USE_COUPON_CODE_OUT_OF_TERM");
					}else{
						//
					}
				}
			}

			if(strlen($error) > 0){
				$cart->setAttribute("discount_free_coupon.error", $error);
				return true;
			}else{
				$cart->clearAttribute("discount_free_coupon.error");
				return false;
			}
		}
	}

	function getForm(CartLogic $cart){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE && strpos($_SERVER["REQUEST_URI"], "/Order/Register")){
			SOY2::import("module.plugins.discount_free_coupon.admin.CouponFormPage");
			$form = SOY2HTMLFactory::createInstance("CouponFormPage");
			$form->setCart($cart);
			$form->execute();
			return array(array("name" => "クーポン", "description" => $form->getObject()));
		}
	}

	function display($orderId){}
	function edit($orderId){}
	function config($orderId){}
}
SOYShopPlugin::extension("soyshop.order.customfield", "discount_free_coupon", "DiscountFreeCouponOrderCustomfieldModule");
