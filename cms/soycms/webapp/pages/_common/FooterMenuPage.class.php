<?php

class FooterMenuPage extends CMSHTMLPageBase{
	
    function __construct() {
    	HTMLPage::HTMLPage();    	
    }
    
    function execute(){
    	
    	$only_one = UserInfoUtil::hasOnlyOneRole();
    	
    	$this->createAdd("admin_link","HTMLLink",array(
    		"link" => SOY2PageController::createRelativeLink("../admin/"),
    		"visible" => !defined("SOYCMS_ASP_MODE") && !$only_one
    	));    	
    }
}
?>