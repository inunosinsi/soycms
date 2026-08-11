<?php
/**
 * @table soyshop_point_history
 */
class SOYShop_PointHistory {

	const POINT_INCREASE = "ポイントを加算しました";
	const POINT_DECREASE = "ポイントを減算しました";
	const POINT_UPDATE = "ポイントに変更しました";
	const POINT_FAILED = "ポイント加算に失敗しました";
	const POINT_PAYMENT = "ポイントを使用しました";

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column order_id
	 */
	private $orderId;

	private $point = 0;
	private $content;

	/**
	 * @column create_date
	 */
	private $createDate;

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserid($userId){
		$this->userId = $userId;
	}

	function getOrderId(){
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getPoint(){
		return $this->point;
	}
	function setPoint($point){
		$this->point = $point;
	}

	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	/**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_point_history";
    }
}
