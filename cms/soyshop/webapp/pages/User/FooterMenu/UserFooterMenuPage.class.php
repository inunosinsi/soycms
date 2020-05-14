<?php

class UserFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		DisplayPlugin::toggle("app_limit_function", AUTH_OPERATE);

		DisplayPlugin::toggle("custom_plugin", (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"))));

		//user.function
		$this->createAdd("function_list", "_common.User.FunctionListComponent", array(
			"list" => self::_getFunctionList()
		));

		//user.info
		$this->createAdd("info_list", "_common.User.InfoListComponent", array(
			"list" => self::_getInfoList()
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
