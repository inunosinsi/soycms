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
     * @column create_date
     */
    private $createDate;
    
    function getUserId(){
    	return $this->userId;
    }
    function setUserId($userId){
    	$this->userId = $userId;
    }
    
    function getOrderId(){
    	return $this->orderId;
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
    
    function getCreateDate(){
    	return $this->createDate;
    }
    function setCreateDate($createDate){
    	$this->createDate = $createDate;
    }
}
?>