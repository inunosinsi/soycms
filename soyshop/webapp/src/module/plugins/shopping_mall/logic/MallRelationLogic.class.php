<?php

class MallRelationLogic extends SOY2LogicBase {

	function __construct(){}

	/**
	 * @param int
	 * @return string
	 */
	function getAdminMailByItemId(int $itemId){
		SOY2::import("module.plugins.shopping_mall.domain.SOYMall_ItemRelationDAO");
		try{
			$adminId = (int)SOY2DAOFactory::create("SOYMall_ItemRelationDAO")->getByItemId($itemId)->getAdminId();
		}catch(Exception $e){
			$adminId = 0;
		}
		
		if($adminId <= 0) return "";

		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAdminDsn();

		try{
			$email = SOY2DAOFactory::create("admin.AdministratorDAO")->getById($adminId)->getEmail();
		}catch(Exception $e){
			$email = "";
		}

		SOYAppUtil::resetAdminDsn($old);

		return $email;
	}
}