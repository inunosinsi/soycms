<?php

class LINELoginUserCustomSearchField extends SOYShopUserCustomfield{

	function getForm($app, int $userId){
		if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) return array();

		SOY2::import("module.plugins.line_login.util.LINELoginUtil");
		$lineId = soyshop_get_user_attribute_value($userId, LINELoginUtil::FIELD_ID, "string");
		return (strlen($lineId)) ? array(array("name" => "LINE ID", "form" => $lineId)) : array();
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","line_login","LINELoginUserCustomSearchField");
