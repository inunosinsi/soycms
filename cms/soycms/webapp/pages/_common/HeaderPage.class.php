<?php

class HeaderPage extends CMSWebPageBase{

	var $title = "";
	
	function setTitle($title){
		$this->title = $title;
	}

    function __construct() {
		WebPage::__construct();    	
    }
    
    function execute(){
    	$this->createAdd("header","HTMLHead",array(
			"title" => UserInfoUtil::getSite()->getSiteName()  . " - SOY CMS",
			"isEraseHead" => false		
		));

    }
}
?>