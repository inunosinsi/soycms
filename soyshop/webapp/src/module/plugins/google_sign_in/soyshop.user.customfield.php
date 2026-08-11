<?php

class GoogleSignInUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, int $userId){
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return array();

		//管理画面のみ
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
		$googleId = soyshop_get_user_attribute_value($userId, GoogleSignInUtil::FIELD_ID, "string");
		return (strlen($googleId)) ? array(array("name" => "Google ID", "form" => $googleId)) : array();
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","google_sign_in","GoogleSignInUserCustomSearchField");
