<?php

class IndexPage extends WebPage{

    function __construct() {
		//DBの初期化に失敗することが多いので再度作成を試みる
		SOY2Logic::createInstance("logic.InitLogic", array(
			"initCheckFile" => SOYINQUIRY_DB_FILE,
		))->checkAndCreateTable();

    	parent::__construct();

    	SOY2::import("domain.SOYInquiry_Inquiry");
    	$this->createAdd("form_list", "_common.FormListComponent", array(
    		"list" => SOY2DAOFactory::create("SOYInquiry_FormDAO")->get()
    	));
    }
}
