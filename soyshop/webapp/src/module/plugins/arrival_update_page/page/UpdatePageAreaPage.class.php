<?php

class UpdatePageAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->createAdd("page_list", "_common.PageListComponent", array(
			"list" => self::_get()
		));
	}

	private function _get(){
		$dao = soyshop_get_hash_table_dao("page");
		$dao->setLimit(5);
		try{
			return $dao->newPages();
		}catch(Exception $e){
			return array();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
