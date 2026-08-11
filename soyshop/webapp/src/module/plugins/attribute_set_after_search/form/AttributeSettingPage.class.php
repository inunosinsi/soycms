<?php

class AttributeSettingPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		parent::__construct();
		
		$selectBefore = (int)SOYShop_DataSets::get("attribute_set_selected_before", 1);
		
		foreach(range(1,3) as $i){
			$this->addCheckBox("user_attribute_" . $i, array(
				"name" => "AttributeSet",
				"value" => $i,
				"selected" => ($selectBefore === $i),
				"label" => "属性" . $i
			));
		}
		
		$this->addCheckBox("clear_value_before_setting", array(
			"name" => "AttributeClear",
			"value" => 1,
			"selected" => SOYShop_DataSets::get("attribute_clear_selected_before", 0),
			"label" => "全顧客の指定の属性の値を削除してから実行"
		));
		
		$this->addInput("set_value", array(
			"name" => "AttributeValue",
			"value" => SOYShop_DataSets::get("attribute_value_selected_before", "")
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>