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
		return $this->userId;
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
