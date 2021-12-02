<?php

/**
 * @table soyshop_user_grouping
 */
class SOYShop_UserGrouping {

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column group_id
	 */
	private $groupId;

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getGroupId(){
		return $this->groupId;
	}
	function setGroupId($groupId){
		$this->groupId = $groupId;
	}
}
