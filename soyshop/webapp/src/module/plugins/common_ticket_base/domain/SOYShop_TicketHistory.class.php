<?php
/**
 * @table soyshop_ticket_history
 */
class SOYShop_TicketHistory {

	const TICKET_INCREASE = "追加しました";
	const TICKET_DECREASE = "減らしました";
	const TICKET_UPDATE = "に変更しました";
	const TICKET_FAILED = "追加に失敗しました";
	const TICKET_PAYMENT = "使用しました";

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column order_id
	 */
	private $orderId;

	private $count = 0;
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

	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
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
    	return "soyshop_ticket_history";
    }
}
