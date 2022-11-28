<?php

class UserGoogleMapCustomFieldModule extends SOYShopUserCustomfield{

	const PLUGIN_ID = "user_google_map";

	/**
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, int $userId){
		if(isset($_POST["user_google_map"]) && is_array($_POST["user_google_map"]) && count($_POST["user_google_map"])){
			foreach($_POST["user_google_map"] as $key => $v){
				$attr = soyshop_get_user_attribute_object($userId, self::PLUGIN_ID . "_" . $key);
				$attr->setValue($v);
				soyshop_save_user_attribute_object($attr);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","user_google_map","UserGoogleMapCustomFieldModule");
