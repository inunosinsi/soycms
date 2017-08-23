<?php

class RemovePage extends SOYShopWebPage{

	private $id;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Check"])){
			
			$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			$site = $this->getSite();
			try{
				$dao->delete($this->id);
				$res = true;
			}catch(Exception $e){
				$res = false;
			}
			
			if($res){
				$logic = SOY2Logic::createInstance("logic.ShopLogic");
				$res = $logic->remove($site);
				
				CMSApplication::jump("Site");
			}
		}
		
		CMSApplication::jump("Site.Remove." . $this->id . "?error");
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
    	
    	$this->addLabel("site_name", array(
    		"text" => $site->getName()
    	));
    	
    	$this->addLabel("site_db", array(
    		"text" => ($site->getIsMysql())? "MySQL": "SQLite"
    	));
    	$this->addLink("site_url", array(
			"link" => $site->getUrl(),
			"text" => $site->getUrl(),
			"target" => "_blank"
		));
    	$this->addCheckBox("check_remove", array(
    		"name" => "Check",
    		"value" => 1,
    		"elementId" => "check_remove"
    	));
    }
    
    function buildMessageForm(){
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
}
?>