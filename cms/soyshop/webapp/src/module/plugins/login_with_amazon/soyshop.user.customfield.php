<?php

class LoginWithAmazonUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, int $userId){
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return;
		
		SOY2::import("module.plugins.login_with_amazon.util.LoginWithAmazonUtil");
		$amazonId = soyshop_get_user_attribute_value($userId, LoginWithAmazonUtil::FIELD_ID, "string");
		return (strlen($amazonId)) ? array(array("name" => "アマゾンID", "form" => $amazonId)) : array();
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","login_with_amazon","LoginWithAmazonUserCustomSearchField");
