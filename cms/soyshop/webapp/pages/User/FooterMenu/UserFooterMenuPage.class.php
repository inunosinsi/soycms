<?php

class UserFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		DisplayPlugin::toggle("app_limit_function", AUTH_CSV);

		DisplayPlugin::toggle("custom_plugin", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"))));

		//user.function
		$this->createAdd("function_list", "_common.User.FunctionListComponent", array(
			"list" => (AUTH_CSV) ? self::_getFunctionList() : array()
		));

		//user.info
		$this->createAdd("info_list", "_common.User.InfoListComponent", array(
			"list" => (AUTH_CSV) ? self::_getInfoList() : array()
		));
	}

	private function _getFunctionList(){
		SOYShopPlugin::load("soyshop.user.function");
		return SOYShopPlugin::invoke("soyshop.user.function", array(
			"mode" => "list"
		))->getList();
	}

	private function _getInfoList(){
		SOYShopPlugin::load("soyshop.user.info");
		return SOYShopPlugin::invoke("soyshop.user.info")->getList();
	}
}
