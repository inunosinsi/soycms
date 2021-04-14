<?php

class CouponHistoryComponent extends HTMLList{

	private $userNameList = array();
	private $trackingNumberList = array();
	private $couponNameList = array();

	protected function populateItem($entity){

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s", $entity->getCreateDate()) : ""
		));

		$this->addLabel("coupon_name", array(
			"text" => (is_numeric($entity->getCouponId()) && isset($this->couponNameList[$entity->getCouponId()])) ? $this->couponNameList[$entity->getCouponId()] : ""
		));

		$orderId = (is_numeric($entity->getOrderId())) ? (int)$entity->getOrderId() : 0;
		$this->addLink("order_tracking_number", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $orderId),
			"text" => (isset($this->trackingNumberList[$orderId])) ? $this->trackingNumberList[$orderId] : ""
		));

		$userId = (is_numeric($entity->getUserId())) ? (int)$entity->getUserId() : 0;
		$this->addLink("customer_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $userId),
			"text" => (isset($this->userNameList[$userId])) ? $this->userNameList[$userId] : ""
		));

		$this->addLabel("coupon_price", array(
			"text" => soy2_number_format(self::_getCouponPrice($entity, $orderId))
		));
	}

	private function _getCouponPrice($history, $orderId){
		if(is_numeric($history->getPrice()) && $history->getPrice() > 0) return $history->getPrice();

		//履歴に値引き額を記録していない場合、1.11.4以前のバージョン対策
		$modules = soyshop_get_order_object($orderId)->getModuleList();
		return (isset($modules["discount_free_coupon"])) ? abs($modules["discount_free_coupon"]->getPrice()) : 0;
	}

	function setCouponNameList($couponNameList){
		$this->couponNameList = $couponNameList;
	}

	function setUserNameList($userNameList){
		$this->userNameList = $userNameList;
	}

	function setTrackingNumberList($trackingNumberList){
		$this->trackingNumberList = $trackingNumberList;
	}

	function setCouponDao($couponDao){
		$this->couponDao = $couponDao;
	}
}
