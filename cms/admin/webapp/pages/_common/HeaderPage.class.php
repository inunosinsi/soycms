<?php

class HeaderPage extends CMSWebPageBase{

	var $title = "";
	
	function setTitle($title){
		$this->title = $title;
	}

    function __construct() {
		WebPage::__construct();    
		
		HTMLHead::addLink("globalpage.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/global_page/globalpage.css") . "?" . SOYCMS_BUILD_TIME
		));
    }
    
    function execute(){
    	$this->createAdd("header", "HTMLHead", array(
			"title" => $this->title,
			"isEraseHead" => false		
		));
    }
}
?>