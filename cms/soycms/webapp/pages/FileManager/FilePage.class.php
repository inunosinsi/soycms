<?php

class FilePage extends CMSWebPageBase{

	function FilePage(){

		WebPage::WebPage();

		$this->addLabel("connector_path", array(
			"text" => SOY2PageController::createRelativeLink("./js/elfinder/php/connector.php") . "?site_id=" . UserInfoUtil::getSite()->getSiteId()
		));

	}

}

?>