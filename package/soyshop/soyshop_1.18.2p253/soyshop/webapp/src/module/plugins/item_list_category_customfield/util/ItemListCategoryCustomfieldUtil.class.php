<?php

class ItemListCategoryCustomfieldUtil{
	
	public static function getPageConfig($moduleId, $pageId){
		return SOYShop_DataSets::get($moduleId . "_" . $pageId . ".config", array(
			"fieldId" => "",
			"useParameter" => 0,
			"fieldValue" => ""
		));
	}
	
	public static function savePageConfig($moduleId, $pageId, $values){
		return SOYShop_DataSets::put($moduleId . "_" . $pageId . ".config", $values);
	}
}
?>