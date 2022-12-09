<?php

class BlackListLogic extends SOY2LogicBase{

	const PLUGIN_ID = "black_customer_list_plugin";
	private $userAttributeDao;

	function __construct(){
		$this->userAttributeDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
	}

	function getBlackList(){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		$sql = "SELECT user.* FROM soyshop_user user ".
				"INNER JOIN soyshop_user_attribute attr ".
				"ON user.id = attr.user_id ".
				"WHERE attr.user_field_id = '" . self::PLUGIN_ID . "' ".
				"AND attr.user_value = 1 ".
				"AND user.is_disabled != 1 ".
				"ORDER BY user.id ASC";

		try{
			$res = $userDao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$users = array();
		foreach($res as $v){
			$users[] = $userDao->getObject($v);
		}

		return $users;
	}

	function getUserIdByOrderId(int $orderId){
		return soyshop_get_order_object($orderId)->getUserId();
	}
}
