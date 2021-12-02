<?php

class SignInLogic extends SOY2LogicBase {

	const FIELD_ID = "social_login_google_sign_in";

	private $userDao;
	private $userAttrDao;

	function __construct(){
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	function getUserByMailAddress(string $mailAddress){
		try{
			return $this->userDao->getByMailAddress($mailAddress);
		}catch(Exception $e){
			$user = new SOYShop_User();
			$user->setMailAddress($mailAddress);
			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
			return $user;
		}
	}

	function registerUser(SOYShop_User $user){
		try{
			return $this->userDao->insert($user);
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
