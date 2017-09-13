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
	 * @query user_id = :userId AND group_id = :groupId
	 */
	abstract function delete($userId, $groupId);

	abstract function deleteByUserId($userId);
}
