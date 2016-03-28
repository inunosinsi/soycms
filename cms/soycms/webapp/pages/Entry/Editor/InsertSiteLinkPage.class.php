<?php

class InsertSiteLinkPage extends CMSWebPageBase{

    function InsertSiteLinkPage() {
    	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		
		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		
		WebPage::WebPage();
		
		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));
		$this->createAdd("jqueryuijs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery-ui.min.js")
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
		
    }
}
?>