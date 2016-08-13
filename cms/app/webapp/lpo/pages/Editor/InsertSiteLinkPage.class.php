<?php

class InsertSiteLinkPage extends CMSWebPageBase{

    function __construct() {
    	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		
		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		
		WebPage::__construct();
		
		$this->createAdd("prototypejs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/prototype.js")
		));
		$this->createAdd("commonjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/common.js")
		));		

		$this->createAdd("urls","HTMLScript",array(
			"type" => "text/JavaScript",
			"script" => 'var link_url = "'.SOY2PageController::createLink("Entry.Editor.InsertLink").'";'
		));
		
		$this->createAdd("site_list","HTMLSelect",array(
			"options"=>$logic->getSiteList(),
			"property"=>"siteName",
			"indexOrder"=>true
		));
		
		$this->createAdd("popupScript","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/tiny_mce/tiny_mce_popup.js")
		));
		
    }
}
?>