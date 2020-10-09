<?php

class RemovePage extends SOYShopWebPage{

	private $id;

	function doPost(){

		if(soy2_check_token() && isset($_POST["Check"])){

			// $site = self::getSite();
			// try{
			// 	self::dao()->delete($this->id);
			// 	$res = true;
			// }catch(Exception $e){
			// 	$res = false;
			// }
			$site = ShopUtil::getSiteById($this->id);

			//if($res){
			SOY2Logic::createInstance("logic.ShopLogic")->remove($site);
			CMSApplication::jump("Site");
			//}
		}

		CMSApplication::jump("Site.Remove." . $this->id . "?error");
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : null;

    	parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

    	self::_buildForm();
    }

    private function _buildForm(){
    	$site = ShopUtil::getSiteById($this->id);

    	$this->addForm("form");

    	$this->addLabel("site_id", array(
    		"text" => $site->getSiteId()
    	));

    	$this->addLabel("site_name", array(
    		"text" => $site->getSiteName()
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
}
