<?php

class RecordDeadLinkConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function RecordDeadLinkConfigFormPage(){
		SOY2::imports("site_include.plugin.record_dead_link.domain.*");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$dao = SOY2DAOFactory::create("RecordDeadLinkDAO");
		$dao->setLimit(5);
		
		try{
			$records = $dao->get();
		}catch(Exception $e){
			$records = array();
		}
		
		SOY2::import("site_include.plugin.record_dead_link.config.RecordListComponent");
		$this->createAdd("record_list", "RecordListComponent", array(
			"list" => $records
		));
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}