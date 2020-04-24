<?php

class CouponHistoryComponent extends HTMLList{
	
	private $userDao;
	private $orderDao;
	private $couponDao;
	
	protected function populateItem($entity){
		
		$coupon = $this->getCoupon($entity->getCouponId());
		$order = $this->getOrder($entity->getOrderId());
		$user = $this->getUser($entity->getUserId());
		
		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getCreateDate())
		));
		
		$this->addLabel("coupon_name", array(
			"text" => $coupon->getName()
		));
		
		$this->addLink("order_tracking_number", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId()),
			"text" => $order->getTrackingNumber()
		));
		
		$this->addLink("customer_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $user->getId()),
			"text" => $user->getName()
		));
		
		$this->addLabel("coupon_price", array(
			"text" => $this->getCouponPrice($entity, $order)
		));
	}
	
	function getCoupon($couponId){
		try{
			$coupon = $this->couponDao->getById($couponId);
		}catch(Exception $e){
			$coupon = new SOYShop_Coupon();
		}
		return $coupon;
	}
	
	function getCouponPrice($history, $order){
		
		if($history->getPrice() > 0) return $history->getPrice();
		
		//履歴に値引き額を記録していない場合、1.11.4以前のバージョン対策
		$modules = $order->getModuleList();
		if(!isset($modules["discount_free_coupon"])) return 0;
		$couponValues = $modules["discount_free_coupon"];
		
		return abs($couponValues->getPrice());
	}
	
	function getOrder($orderId){
		try{
			$order = $this->orderDao->getById($orderId);
		}catch(Exception $e){
			$order = new SOYShop_Order();
		}
		return $order;
	}
	
	function getUser($userId){
		try{
			$user = $this->userDao->getById($userId);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
		return $user;
	}
	
	function setUserDao($userDao){
		$this->userDao = $userDao;
	}
	
	function setOrderDao($orderDao){
		$this->orderDao = $orderDao;
	}
	
	function setCouponDao($couponDao){
		$this->couponDao = $couponDao;
	}
}
?>