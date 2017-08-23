<?php

class InsertSiteLinkPage extends CMSWebPageBase{

	function __construct() {

		//ASPでは使用不可
		if(defined("SOYCMS_ASP_MODE")) exit;

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");

		parent::__construct();

		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->createAdd("urls","HTMLScript",array(
			"type" => "text/JavaScript",
			"script" => 'var link_url = "'.SOY2PageController::createLink("Entry.Editor.InsertLink").'";'
		));

		$this->createAdd("site_list","HTMLSelect",array(
			"options"=>$logic->getSiteOnly(),
			"property"=>"siteName",
			"indexOrder"=>true
		));

	}
}
