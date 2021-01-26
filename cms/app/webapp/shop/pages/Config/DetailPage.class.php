<?php

class DetailPage extends SOYShopWebPage{

	private $id;
	private $siteId;

	function doPost(){

		if(soy2_check_token() && isset($_POST["Account"])){

			if(SOY2Logic::createInstance("logic.RoleLogic")->updateSiteRole($_POST["Account"], $this->id)){
				CMSApplication::jump("Config.Detail." . $this->id . "?updated");
			}
		}
		CMSApplication::jump("Config.Detail." . $this->id . "?error");
	}

    function __construct($args) {
    	$this->id = $args[0];

		$site = ShopUtil::getSiteById($this->id);

    	parent::__construct();

    	$this->addLabel("site_name", array(
    		"text" => $site->getSiteName()
    	));

		foreach(array("updated", "error") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

    	$this->addForm("form");

		$logic = SOY2Logic::createInstance("logic.RoleLogic");

    	//Shop用サイトの管理権限はSiteRoleを使用する
    	$this->createAdd("account_list", "_common.SOYShop_SiteAccountList", array(
			"list" => $logic->getAccounts("site", $this->id),
			"role" => $logic->getSiteRoleArray($site)
		));
    }
}
