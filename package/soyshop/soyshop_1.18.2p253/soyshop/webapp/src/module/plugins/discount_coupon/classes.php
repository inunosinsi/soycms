<?php

class SOYShopCouponConfig{
	
	private $acceptNumber = 1;
	private $enableAmountMin = 0;
	private $enableAmountMax = null;

	/**#@+
	 * 
	 * @access public 
	 */
	public function getAcceptNumber() {
		return $this->acceptNumber;
	}
	public function setAcceptNumber($acceptNumber) {
		$this->acceptNumber = $acceptNumber;
	}
	public function getEnableAmountMin() {
		return $this->enableAmountMin;
	}
	public function setEnableAmountMin($enableAmountMin) {
		$this->enableAmountMin = $enableAmountMin;
	}
	public function getEnableAmountMax() {
		return $this->enableAmountMax;
	}
	public function setEnableAmountMax($enableAmountMax) {
		$this->enableAmountMax = $enableAmountMax;
	}
	/**#@-*/

}

class SOYShopCoupon{
	
	private $id;
	private $number;
	private $value;
	private $title;
	private $memo;
	private $expirationDate;//yyyy-mm-dd
	private $expirationDatetime;//unixtime
	private $issuedDatetime;//unixtime
	
	private $couponCodes = array();// "code" => SOYShopCouponCode

	/**#@+
	 * 
	 * @access public 
	 */
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getNumber() {
		return $this->number;
	}
	public function setNumber($number) {
		$this->number = $number;
	}
	public function getValue() {
		return $this->value;
	}
	public function setValue($value) {
		$this->value = $value;
	}
	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getMemo() {
		return $this->memo;
	}
	public function setMemo($memo) {
		$this->memo = $memo;
	}
	public function getExpirationDate() {
		return $this->expirationDate;
	}
	public function setExpirationDate($expirationDate) {
		$this->expirationDate = $expirationDate;
		$this->expirationDatetime = strtotime(date("Y-m-d 23:59:59",strtotime($expirationDate)));
	}
	public function getExpirationDatetime() {
		return $this->expirationDatetime;
	}
	public function setExpirationDatetime($expirationDatetime) {
		$this->expirationDatetime = $expirationDatetime;
	}
	public function getIssuedDatetime() {
		return $this->issuedDatetime;
	}
	public function getIssuedDate() {
		return date("Y-m-d H:i:s", $this->issuedDatetime);
	}
	public function setIssuedDatetime($issuedDatetime) {
		$this->issuedDatetime = $issuedDatetime;
	}
	public function getCouponCodes() {
		return $this->couponCodes;
	}
	public function setCouponCodes($couponCodes) {
		$this->couponCodes = $couponCodes;
	}
	/**#@-*/

}


class SOYShopCouponCode{
	
	private $code;
	private $couponId;
	private $status = self::STATUS_ISSUED;
	private $orderId;
	private $userId;
	
//	private $order;
//	private $user;
	
	const STATUS_ISSUED    = 1;
	const STATUS_PUBLISHED = 2;
	const STATUS_USED      = 3;
	const STATUS_VOID      = 4;

	static $STATUS_ARRAY = array(
		1 => "未配布",
		2 => "配布済",
		3 => "利用済",
		4 => "失効",
	);

	/**#@+
	 * 
	 * @access public 
	 */
	public function getCode() {
		return $this->code;
	}
	public function setCode($code) {
		$this->code = $code;
	}
	public function getCouponId() {
		return $this->couponId;
	}
	public function setCouponId($couponId) {
		$this->couponId = $couponId;
	}
	public function getStatus() {
		return $this->status;
	}
	public function getStatusText() {
		return self::$STATUS_ARRAY[$this->status];
	}
	public function setStatus($status) {
		$this->status = $status;
	}
	public function getOrderId() {
		return $this->orderId;
	}
	public function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
	public function getUserId() {
		return $this->userId;
	}
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	/**#@-*/


//	public function getOrder(){
//		if(strlen($this->orderId)){
//	    	try{
//		    	$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
//		    	$order = $logic->getById($this->id);
//	    	}catch(Exception $e){
//	    		return "";
//	    	}
//			
//		}
//	}
}

