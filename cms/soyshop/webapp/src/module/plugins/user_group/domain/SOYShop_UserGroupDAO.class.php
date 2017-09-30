<?php

/**
 * @entity SOYShop_UserGroup
 */
abstract class SOYShop_UserGroupDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYShop_UserGroup $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_UserGroup $bean);

	/**
	 * @query is_disabled != 1
	 */
	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @final
	 */
	function getGroupsByUserId($userId){
		$sql = "SELECT g.* FROM soyshop_user_group g ".
				"INNER JOIN soyshop_user_grouping gi ".
				"ON g.id = gi.group_id ".
				"WHERE gi.user_id = :userId ".
				"AND is_disabled != " . SOYShop_UserGroup::IS_DISABLED;
		try{
			$results = $this->executeQuery($sql, array(":userId" => $userId));
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) return array();

		$groups = array();
		foreach($results as $v){
			$groups[] = $this->getObject($v);
		}
		return $groups;
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!isset($binds[":order"]) || !is_numeric($binds[":order"])) $binds[":order"] = 0;
		if(is_null($binds[":isDisabled"])) $binds[":isDisabled"] = 0;

		return array($query, $binds);
	}
}
