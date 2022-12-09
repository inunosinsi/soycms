<?php
/**
 * @table soyshop_coupon_history
 */
class SOYShop_CouponHistory {

    /**
     * @column user_id
     */
    private $userId;

    /**
     * @column coupon_id
     */
    private $couponId;

    /**
     * @column order_id
     */
    private $orderId;
    private $price;

	/**
	 * @column is_free_delivery
	 */
    private $isFreeDelivery;

    /**
     * @column create_date
     */
    private $createDate;

    function getUserId(){
    	return (is_numeric($this->userId)) ? (int)$this->userId : 0;
    }
    function setUserId($userId){
    	$this->userId = $userId;
    }

    function getOrderId(){
    	return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
    }
    function setOrderId($orderId){
    	$this->orderId = $orderId;
    }

    function getCouponId(){
    	return $this->couponId;
    }
    function setCouponId($couponId){
    	$this->couponId = $couponId;
    }

    function getPrice(){
    	return $this->price;
    }
    function setPrice($price){
    	$this->price = $price;
    }

	function getIsFreeDelivery(){
		return $this->isFreeDelivery;
	}
	function setIsFreeDelivery($isFreeDelivery){
		$this->isFreeDelivery = $isFreeDelivery;
	}

    function getCreateDate(){
    	return $this->createDate;
    }
    function setCreateDate($createDate){
    	$this->createDate = $createDate;
    }
}
