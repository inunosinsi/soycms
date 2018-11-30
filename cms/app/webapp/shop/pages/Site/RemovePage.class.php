<?php

class RemovePage extends SOYShopWebPage{

	private $id;

	function doPost(){

		if(soy2_check_token() && isset($_POST["Check"])){

			$site = self::getSite();
			try{
				self::dao()->delete($this->id);
				$res = true;
			}catch(Exception $e){
				$res = false;
			}

			if($res){
				$logic = SOY2Logic::createInstance("logic.ShopLogic")->remove($site);
				CMSApplication::jump("Site");
			}
		}

		CMSApplication::jump("Site.Remove." . $this->id . "?error");
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : null;

    	parent::__construct();

    	self::buildMessageForm();
    	self::buildForm();
    }

    private function buildForm(){
    	$site = self::getSite();

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

    private function buildMessageForm(){
		DisplayPlugin::toggle("error", isset($_GET["error"]));
    }

    private function getSite(){
    	try{
    		return self::dao()->getById($this->id);
    	}catch(Exception $e){
    		return new SOYShop_Site();
    	}
    }

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
		return $dao;
	}
}
