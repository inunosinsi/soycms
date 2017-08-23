<?php

class MenuPage extends CMSWebPageBase{
	
	var $type = SOY2HTML::SOY_BODY;
	
	function __construct(){
		parent::__construct();
	}
	
	function execute(){
				
		$this->addLink("administratorlink", array(
			"link" => SOY2PageController::createLink("Administrator.List")
		));
		
		$this->addLink("sitelink", array(
			"link" => SOY2PageController::createLink("Site.List")
		));
		
		$this->addLink("siterolelink", array(
			"link" => SOY2PageController::createLink("SiteRole.List")
		));
	}
}
?>