<?php

SOY2HTMLFactory::importWebPage("_common.FormList");

class IndexPage extends WebPage{

    function __construct() {
    	WebPage::__construct();
    	
    	$formDAO = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$forms = $formDAO->get();
    	
    	SOY2::import("domain.SOYInquiry_Inquiry");
    	$this->createAdd("form_list","FormList",array(
    		"list" => $forms    	
    	));	
    }
}



?>