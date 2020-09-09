<?php
class IndexPage extends WebPage{

	function __construct(){
		parent::__construct();

		$this->addLink("login_link", array(
			"link" => SOY2PageController::createRelativeLink("../soyshop?site_id=".SOYSHOP_ID)
		));
	}
}
