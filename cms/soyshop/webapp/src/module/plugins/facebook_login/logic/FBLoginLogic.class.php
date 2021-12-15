<?php

class FbLoginLogic extends SOY2LogicBase {

	function __construct(){}

	function getUserByMailAddress(string $mailAddress){
		return soyshop_get_user_object_by_mailaddress($mailAddress);
	}

	function registerUser(SOYShop_User $user){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->insert($user);
		}catch(Exception $e){
			return null;
		}
	}

	function saveFacebookId(int $userId, string $facebookId){
		SOY2::import("module.plugins.facebook_login.util.FacebookLoginUtil");
		$attr = soyshop_get_user_attribute_object($userId, FacebookLoginUtil::FIELD_ID);
		$attr->setValue($facebookId);
		soyshop_save_user_attribute_object($attr);
	}
}
