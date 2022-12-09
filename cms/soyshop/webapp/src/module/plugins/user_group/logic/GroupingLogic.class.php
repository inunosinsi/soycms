<?php

class GroupingLogic extends SOY2LogicBase {

	function __construct(){}

	function getUsersByGroupId($groupId){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$sql = "SELECT user.* FROM soyshop_user user ".
				"INNER JOIN soyshop_user_grouping gr ".
				"on user.id = gr.user_id ".
				"WHERE gr.group_id = :groupId ".
				"AND user.is_disabled = " . SOYShop_User::USER_NOT_DISABLED . " ".
				"AND user.user_type = " . SOYShop_User::USERTYPE_REGISTER;
		try{
			$res = $userDao->executeQuery($sql, array(":groupId" => $groupId));
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$users = array();
		foreach($res as $v){
			$users[] = $userDao->getObject($v);
		}
		return $users;
	}
}
