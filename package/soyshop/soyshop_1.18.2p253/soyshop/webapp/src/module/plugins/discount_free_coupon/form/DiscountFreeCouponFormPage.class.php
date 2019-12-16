<?php

class DiscountFreeCouponFormPage extends WebPage{

	private $pluginObj;
	private $orderId;

	function __construct(){}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("not_used_coupon", (!self::checkUsedCoupon() && self::checkUsingCouponByOrderPaymentStatus()));

		$this->addInput("coupon", array(
			"name" => "discountFreeCoupon",
			"value" => ""
		));
	}

	//開いた注文詳細でクーポンコードが使用されているか？調べる。使用されていなければ
	private function checkUsedCoupon(){
		$modules = self::getOrderById($this->orderId)->getModuleList();
		if(!count($modules)) return false;

		foreach($modules as $moduleId => $module){
			if(strpos($moduleId, "free_coupon")) return true;
		}

		return false;
	}

	//該当する注文が支払い前であるか？支払待ち、入金エラーと直接支払いを支払い前と見なす
	private function checkUsingCouponByOrderPaymentStatus(){
		$status = (int)self::getOrderById($this->orderId)->getPaymentStatus();
		return ($status === SOYShop_Order::PAYMENT_STATUS_WAIT || $status === SOYShop_Order::PAYMENT_STATUS_ERROR || $status === SOYShop_Order::PAYMENT_STATUS_REFUNDED);
	}

	private function getOrderById($orderId){
		static $order;
		if(is_null($order)){
			try{
				$order = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($orderId);
			}catch(Exception $e){
				$order = new SOYShop_Order();
			}
		}
		return $order;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
