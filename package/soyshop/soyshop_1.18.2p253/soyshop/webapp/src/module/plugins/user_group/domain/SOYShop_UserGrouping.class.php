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
		return $this->userId;
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
