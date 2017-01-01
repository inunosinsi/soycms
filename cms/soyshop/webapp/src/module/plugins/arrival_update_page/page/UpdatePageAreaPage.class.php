<?php

class UpdatePageAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		$pageDAO = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$pageDAO->setLimit(5);

		try{
			$pages = $pageDAO->newPages();
		}catch(Exception $e){
			$pages = array();
		}

		$this->createAdd("page_list", "_common.PageListComponent", array(
			"list" => $pages
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>