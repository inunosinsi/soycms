<?php
SOY2::import("domain.admin.Site");

class IndexPage extends CMSWebPageBase{

	function __construct(){
		parent::__construct();

		//アプリケーション
		$applications = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic")->getLoginiableApplicationLists();
		$this->createAdd("application_list", "_common.Application.ApplicationListComponent", array(
			"list" => $applications
		));

		$this->addModel("no_application", array(
			"visible" => (count($applications) < 1)
		));

	}
}
