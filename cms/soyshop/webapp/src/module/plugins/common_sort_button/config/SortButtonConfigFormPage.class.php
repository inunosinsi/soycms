<?php

class SortButtonConfigFormPage extends WebPage{
	
	private $obj;
	
	function __construct(){
		
	}
	
	function doPost(){
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		SOY2::import("module.plugins.common_sort_button.util.SortButtonUtil");
		$list = SortButtonUtil::getColumnList();
		
		$this->createAdd("column_list", "ColumnListComponent", array(
			"list" => $list
		));
		
		$this->createAdd("sample_code_list", "ColumnListComponent", array(
			"list" => $list
		));
	}
	
	function setConfigObj($obj){
		$this->obj = $obj;
	}
}

class ColumnListComponent extends HTMLList{
	
	function populateItem($entity, $key){
		
		$this->addLabel("column_name_desc", array(
			"text" => "sort_" . $key . "_desc"
		));
		
		$this->addLabel("column_name", array(
			"text" => $entity
		));
		
		$this->addLabel("column_name_desc_text", array(
			"text" => $entity . "で降順表示に変更します。"
		));
		
		$this->addLabel("column_name_asc", array(
			"text" => "sort_" . $key . "_asc"
		));
		
		$this->addLabel("column_name_asc_text", array(
			"text" => $entity . "で昇順表示に変更します。"
		));
	}
}
?>