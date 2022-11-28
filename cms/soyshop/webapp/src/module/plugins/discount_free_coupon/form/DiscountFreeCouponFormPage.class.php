<?php

class DiscountFreeCouponFormPage extends WebPage{

	private $pluginObj;
	private $orderId;

	function __construct(){}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("not_used_coupon", (!self::_checkUsedCoupon() && self::_checkUsingCouponByOrderPaymentStatus()));

		$this->addInput("coupon", array(
			"name" => "discountFreeCoupon",
			"value" => ""
		));
	}

	//開いた注文詳細でクーポンコードが使用されているか？調べる。使用されていなければfalse
	private function _checkUsedCoupon(){
		$modules = soyshop_get_order_object($this->orderId)->getModuleList();
		if(!count($modules)) return false;

		foreach($modules as $moduleId => $module){
			if(strpos($moduleId, "free_coupon")) return true;
		}

		return false;
	}

	//該当する注文が支払い前であるか？支払待ち、入金エラーと直接支払いを支払い前と見なす
	private function _checkUsingCouponByOrderPaymentStatus(){
		//入力フォームを常に表示するか？
		SOY2::import("module.plugins.discount_free_coupon.util.DiscountFreeCouponUtil");
		$cnf = DiscountFreeCouponUtil::getConfig();
		if(isset($cnf["displayAlways"]) && (int)$cnf["displayAlways"] === DiscountFreeCouponUtil::DISPLAY_ITEM_ALWAYS) return true;

		$status = (int)soyshop_get_order_object($this->orderId)->getPaymentStatus();
		return ($status === SOYShop_Order::PAYMENT_STATUS_WAIT || $status === SOYShop_Order::PAYMENT_STATUS_ERROR || $status === SOYShop_Order::PAYMENT_STATUS_REFUNDED);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
