<?php
/**
 * @table soyshop_download
 */
class SOYShop_Download {

	/**
	 * @id
	 */
    private $id;

    /**
     * @column order_id
     */
    private $orderId;

    /**
     * @column item_id
     */
    private $itemId;

    /**
     * @column user_id
     */
    private $userId;

	/**
	 * @column file_name
	 */
	private $fileName;

    private $token;

    /**
     * @column order_date
     */
    private $orderDate;

    /**
     * @column received_date
     */
    private $receivedDate;
    /**
     * @column time_limit
     */
    private $timeLimit;

    private $count;

    function getId(){
    	return $this->id;
    }
    function setId($id){
    	$this->id = $id;
    }

    function getOrderId(){
    	return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
    }
    function setOrderId($orderId){
    	$this->orderId = $orderId;
    }

    function getItemId(){
    	return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
    }
    function setItemId($itemId){
    	$this->itemId = $itemId;
    }

    function getUserId(){
    	return (is_numeric($this->userId)) ? (int)$this->userId : 0;
    }
    function setUserId($userId){
    	$this->userId = $userId;
    }

    function getFileName(){
    	return $this->fileName;
    }
    function setFileName($fileName){
    	$this->fileName = $fileName;
    }

    function getToken(){
    	return $this->token;
    }
    function setToken($token){
    	$this->token = $token;
    }

    function getOrderDate(){
    	return $this->orderDate;
    }
    function setOrderDate($orderDate){
    	$this->orderDate = $orderDate;
    }

    function getReceivedDate(){
    	return $this->receivedDate;
    }
    function setReceivedDate($receivedDate){
    	$this->receivedDate = $receivedDate;
    }

    function getTimeLimit(){
    	return $this->timeLimit;
    }
    function setTimeLimit($timeLimit){
    	$this->timeLimit = $timeLimit;
    }

    function getCount(){
    	return $this->count;
    }
    function setCount($count){
    	$this->count = $count;
    }
}
