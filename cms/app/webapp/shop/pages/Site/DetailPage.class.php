<?php

class DetailPage extends SOYShopWebPage{

	private $id;
	private $dao;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Site"])){
			$site = $this->getSite();
			
			$dao = $this->dao;
			$site = SOY2::cast($site, $_POST["Site"]);
			
			/**
			 * shopディレクトリにあるsqlite.dbのショップ名の書き換え
			 * shop.dbの書き換え
			 * cms.dbの書き換え
			 */
			$logic = SOY2Logic::createInstance("logic.ShopLogic");
			$res = $logic->updateShopSite($site);
			
			if($res){
				CMSApplication::jump("Site.Detail." . $this->id . "?updated");
			}else{
				CMSApplication::jump("Site.Detail." . $this->id . "?error");
			}
		}
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : null;
   
    	parent::__construct();
    	
    	$this->buildMessageForm();
    	$this->buildForm();
    }
    
    function buildForm(){
    	$site = $this->getSite();
    	
    	$this->addForm("form");
    	
    	$this->addLabel("site_id", array(
    		"text" => $site->getSiteId()
    	));
    	
    	$this->addInput("site_name", array(
    		"name" => "Site[name]",
    		"value" => $site->getName(),
    		"style" => "width:95%"
    	));
    	
    	$this->addLabel("site_db", array(
    		"text" => ($site->getIsMysql())? "MySQL": "SQLite"
    	));
    	
    	$logic = SOY2Logic::createInstance("logic.ShopLogic");
		$checkHasRootSite = $logic->checkHasRootSite();	
    	$checkIsRootSite = $logic->checkIsRootSite($site->getSiteId());

    	$this->addLink("site_root_link", array(
    		"link" => $this->createRootLink($site),
			"text" => $this->createRootLink($site),
			"target" => "_blank",
			"visible" => ($checkIsRootSite)
    	));
    	$this->addModel("is_root", array(
    		"visible" => ($checkIsRootSite)
    	));
    	
		$this->addLink("site_url", array(
			"link" => $site->getUrl(),
			"text" => $site->getUrl(),
			"target" => "_blank"
		));
		
		$this->addInput("site_url_input", array(
			"name" => "Site[url]",
			"value" => $site->getUrl(),
			"id" => "site_url",
			"style" => "width:95%"
		));
		
    	include_once(str_replace("common", "soyshop", CMS_COMMON) . "webapp/conf/shop/" . $site->getSiteId() . ".conf.php");    	
    	$this->addLabel("default_url", array(
    		"text" => SOYSHOP_SITE_URL,
    		"id" => "default_url"
    	));
		
		$this->addActionLink("controller_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.CreateController." . $site->getId()),
			"text" => "再生成を実行"
		));
		
		$this->addModel("config_root", array(
			"visible" => (!$checkHasRootSite)
		));
		
		$this->addActionLink("set_root_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.SiteRoot." . $site->getId()),
			"text" => "ルート設定を行う",
			"visible" => (!$checkIsRootSite),
			"onclick"=> 'return confirm("ドメインルートに設定します。よろしいですか？");',
		));
		$this->addActionLink("detach_root_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.SiteRootDetach." . $site->getId()),
			"text" => "ルート設定を解除する",
			"visible" => ($checkIsRootSite),
			"onclick"=> 'return confirm("ドメインルートの設定を解除します。");',
		));
		
		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Config.Detail." . $site->getId()),
			"text" => "詳細"
		));
    	
    }
    
    function buildMessageForm(){
    	$this->addModel("success", array(
    		"visible" => (isset($_GET["success"]))
    	));
    	$this->addModel("detach", array(
    		"visible" => (isset($_GET["detach"]))
    	));
    	$this->addModel("created", array(
    		"visible" => (isset($_GET["created"]))
    	));
    	$this->addModel("updated", array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	$this->addModel("error", array(
    		"visible" => (isset($_GET["error"]))
    	));
    }
    
    function getSite(){
    	$this->dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
    	try{
    		$site = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		$site = new SOYShop_Site();
    	}
    	
    	return $site;
    }
    
    function createRootLink($site){
    	
    	$siteId = $site->getSiteId();
    	$siteUrl = $site->getUrl();
    	
    	return str_replace($siteId . "/", "", $siteUrl);
    	
    }
}
?>