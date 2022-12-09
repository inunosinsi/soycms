<?php
/**
 * @table soyshop_mypage_login_log
 */
class SOYShop_MypageLoginLog {

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column log_date
	 */
	private $logDate;

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getLogDate(){
		return $this->logDate;
	}
	function setLogDate($logDate){
		$this->logDate = $logDate;
	}
}
