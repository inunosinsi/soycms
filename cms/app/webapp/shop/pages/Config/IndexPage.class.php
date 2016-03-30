<?php

class IndexPage extends SOYShopWebPage{

	function doPost(){

		if(soy2_check_token()&&isset($_POST["Account"])){
			$accounts = $_POST["Account"];
			$logic = SOY2Logic::createInstance("logic.ShopLogic");
			$res = $logic->updateAppRole($accounts);

			if($res){
				CMSApplication::jump("Config?updated");
			}else{
				CMSApplication::jump("Config?error");
			}

		}

	}

	function IndexPage(){
		WebPage::WebPage();

		$this->buildForm();

	}


	function buildForm(){

		$logic = SOY2Logic::createInstance("logic.ShopLogic");
		$accounts = $logic->getAccounts("app");

		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));

		$this->addForm("form");

		$this->createAdd("account_list", "_common.SOYShop_AppAccountList",array(
			"list" => $accounts,
			"role" => $logic->getAppRoleArray()
		));
	}
}
?>