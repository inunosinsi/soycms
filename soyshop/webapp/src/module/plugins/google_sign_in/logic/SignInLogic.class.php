<?php

class SignInLogic extends SOY2LogicBase {

	const FIELD_ID = "social_login_google_sign_in";

	function __construct(){
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
	}

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

	function saveGoogleId(int $userId, string $googleId){
		$attr = soyshop_get_user_attribute_object($userId, GoogleSignInUtil::FIELD_ID);
		$attr->setValue($googleId);
		soyshop_save_user_attribute_object($attr);
	}
}
