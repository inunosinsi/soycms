<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class FacebookLoginUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, int $userId){
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return array();

		SOY2::import("module.plugins.facebook_login.util.FacebookLoginUtil");
		$facebookId = soyshop_get_user_attribute_value($userId, FacebookLoginUtil::FIELD_ID, "string");
		return (strlen($facebookId)) ? array(array("name" => "Facebook ID", "form" => $facebookId)) : array();
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","facebook_login","FacebookLoginUserCustomSearchField");
