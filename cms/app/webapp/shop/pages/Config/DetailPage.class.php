<?php

class DetailPage extends SOYShopWebPage{

	private $id;
	private $siteId;

	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Account"])){
			$accounts = $_POST["Account"];
			$logic = SOY2Logic::createInstance("logic.ShopLogic");
			$res = $logic->updateSiteRole($accounts, $this->siteId);
			
			if($res){
				CMSApplication::jump("Config.Detail." . $this->id . "?updated");
			}else{
				CMSApplication::jump("Config.Detail." . $this->id . "?error");
			}
		}
	}

    function __construct($args) {
    	$this->id = $args[0];
    	
    	$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
    	try{
    		$site = $dao->getById($this->id);
    	}catch(Exception $e){
    		$site = new SOYShop_Site();
    	}
    	
    	$this->siteId = $site->getSiteId();
    	
    	parent::__construct();
    	
    	$this->addLabel("site_name", array(
    		"text" => $site->getName()
    	));
    	
    	$this->addModel("updated", array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	$this->addModel("error", array(
    		"visible" => (isset($_GET["error"]))
    	));
    	
    	$this->addForm("form");
    	
    	//Shop用サイトの管理権限はSiteRoleを使用する
    	$logic = SOY2Logic::createInstance("logic.ShopLogic");
    	$accounts = $logic->getAccounts("site", $this->siteId);
    	
    	$this->createAdd("account_list", "_common.SOYShop_SiteAccountList", array(
			"list" => $accounts,
			"role" => $logic->getSiteRoleArray()
		));
    }
}
?>