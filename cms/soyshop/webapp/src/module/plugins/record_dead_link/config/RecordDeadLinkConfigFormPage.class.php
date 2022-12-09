<?php

class RecordDeadLinkConfigFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::imports("module.plugins.record_dead_link.domain.*");
	}
	
	function execute(){
		parent::__construct();
		
		$dao = SOY2DAOFactory::create("SOYShop_RecordDeadLinkDAO");
		$dao->setLimit(5);
		
		try{
			$records = $dao->get();
		}catch(Exception $e){
			$records = array();
		}
		
		SOY2::import("module.plugins.record_dead_link.config.RecordListComponent");
		$this->createAdd("record_list", "RecordListComponent", array(
			"list" => $records
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>