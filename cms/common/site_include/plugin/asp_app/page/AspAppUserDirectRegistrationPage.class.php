<?php

class AspAppUserDirectRegistrationPage extends WebPage {

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
	}

	function execute(){
		parent::__construct();

		AspAppUtil::clearSession("hidden_mode");
		AspAppUtil::clear();

		$cmsDir = AspAppUtil::getSession("cms_dir");
		AspAppUtil::clearSession("cms_dir");

		DisplayPlugin::toggle("has_cms_dir", strlen($cmsDir));

		$this->addLink("cms_admin_link", array(
			"link" => "/" . $cmsDir . "/admin/"
		));
	}

	/** @ToDo テンプレート編集モード **/
}
