<?php
/**
 * @table soyshop_mail_log
 */
class SOYShop_MailLog {
	
	const SUCCESS = 1;
	const FAILED = 0;

	/**
	 * @column id
	 */
    private $id;
    private $recipient;
    
    /**
     * @column order_id
     */
    private $orderId;
    
    /**
     * @column user_id
     */
    private $userId;
    private $title;
    private $content;
    
    /**
     * @column is_success
     */
    private $isSuccess = 0;

	/**
	 * @column send_date
	 */
	private $sendDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getRecipient(){
		return $this->recipient;
	}
	function setRecipient($recipient){
		$this->recipient = $recipient;
	}
	
	function getOrderId(){
		return $this->orderId;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
	
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	
	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}
	
	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}
	
	function getIsSuccess(){
		return $this->isSuccess;
	}
	function setIsSuccess($isSuccess){
		$this->isSuccess = $isSuccess;
	}
	
	function getSendDate(){
		return $this->sendDate;
	}
	function setSendDate($sendDate){
		$this->sendDate = $sendDate;
	}
	
	/**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_mail_log";
    }
}
?>