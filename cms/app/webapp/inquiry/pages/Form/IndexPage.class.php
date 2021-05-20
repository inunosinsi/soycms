<?php

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

    	SOY2::import("domain.SOYInquiry_Inquiry");
    	$this->createAdd("form_list", "_common.FormListComponent", array(
    		"list" => SOY2DAOFactory::create("SOYInquiry_FormDAO")->get()
    	));
    }
}
