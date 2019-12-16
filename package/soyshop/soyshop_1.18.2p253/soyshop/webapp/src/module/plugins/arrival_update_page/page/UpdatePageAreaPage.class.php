<?php

class UpdatePageAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$pageDao->setLimit(5);
		try{
			$pages = $pageDao->newPages();
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
