<?php

class CommonMailbuilderCommon{

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	public static function getSortConfig(){
		return SOYShop_DataSets::get("common_mailbuilder.sort_config", array("defaultSort" => "code", "isReverse" => 0));
	}

	public static function saveSortConfig($values){
		SOYShop_DataSets::put("common_mailbuilder.sort_config", $values);
	}

	public static function getMailContent($type = "user"){
		$content = SOYShop_DataSets::get("common_mailbuilder_" . $type, null);
		if(is_null($content)) $content = file_get_contents(dirname(__FILE__) . "/content.txt");
		return $content;
	}

	public static function saveMailContent($content, $type = "user"){
		SOYShop_DataSets::put("common_mailbuilder_" . $type, $content);
	}
}
