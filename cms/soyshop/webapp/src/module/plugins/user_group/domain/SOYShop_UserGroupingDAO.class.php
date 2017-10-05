<?php

/**
 * @entity SOYShop_UserGrouping
 */
abstract class SOYShop_UserGroupingDAO extends SOY2DAO{

	abstract function insert(SOYShop_UserGrouping $bean);

	abstract function getByUserId($userId);

	abstract function getByGroupId($groupId);

	/**
	 * @return column_count_user
	 * @columns count(*) as count_user
	 * @query group_id = :groupId
	 */
	abstract function countByGroupId($groupId);

	/**
	 * @final
	 */
	function getUsersByGroupId($groupId){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$sql = "SELECT u.* FROM soyshop_user u ".
				"INNER JOIN soyshop_user_grouping gi ".
				"ON u.id = gi.user_id ".
				"WHERE gi.group_id = :groupId ".
				"AND u.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"AND u.is_publish != " . SOYShop_User::USER_NO_PUBLISH;
		try{
			$results = $userDao->executeQuery($sql, array(":groupId" => $groupId));
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) return array();

		$users = array();
		foreach($results as $v){
			$users[] = $userDao->getObject($v);
		}
		return $users;
	}

	/**
	 * @query user_id = :userId AND group_id = :groupId
	 */
	abstract function delete($userId, $groupId);

	abstract function deleteByUserId($userId);
}
