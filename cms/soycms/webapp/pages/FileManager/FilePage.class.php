<?php

class FilePage extends CMSWebPageBase{

	function __construct(){

		parent::__construct();

		$this->addLabel("base_dir_path", array(
			"text" => SOY2PageController::createRelativeLink("./js/elfinder/")
		));

		$this->addLabel("connector_path", array(
			"text" => SOY2PageController::createRelativeLink("./js/elfinder/php/connector.php") . "?site_id=" . UserInfoUtil::getSite()->getSiteId()
		));
	}
}
