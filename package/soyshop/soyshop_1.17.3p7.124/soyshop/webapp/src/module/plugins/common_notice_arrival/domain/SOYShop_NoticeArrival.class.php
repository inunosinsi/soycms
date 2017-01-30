<?php

/**
 * @table soyshop_notice_arrival
 */
class SOYShop_NoticeArrival{
	
	const NOT_SENDED = 0;
	const SENDED = 1;
	
	const NOT_CHECKED = 0;
	const CHECKED = 1;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column item_id
	 */
	private $itemId;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	private $sended;
	private $checked;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	
	function getSended(){
		return $this->sended;
	}
	function setSended($sended){
		$this->sended = $sended;
	}
	
	function getChecked(){
		return $this->checked;
	}
	function setChecked($checked){
		$this->checked = $checked;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
?>