<?php

class IndexPage extends SOYShopWebPage{

	function doPost(){

		if(soy2_check_token()&&isset($_POST["Account"])){
			if(SOY2Logic::createInstance("logic.ShopLogic")->updateAppRole($_POST["Account"])){
				CMSApplication::jump("Config?updated");
			}else{
				CMSApplication::jump("Config?error");
			}
		}
	}

	function __construct(){
		parent::__construct();

		self::buildForm();

	}

	private function buildForm(){

		$logic = SOY2Logic::createInstance("logic.ShopLogic");
		$accounts = $logic->getAccounts("app");

		foreach(array("updated", "error") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		$this->addForm("form");

		$this->createAdd("account_list", "_common.SOYShop_AppAccountList",array(
			"list" => $accounts,
			"role" => $logic->getAppRoleArray()
		));
	}
}
