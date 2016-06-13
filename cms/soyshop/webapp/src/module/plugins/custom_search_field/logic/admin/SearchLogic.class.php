<?php

class SearchLogic extends SOY2LogicBase{

	private $fieldId;
	private $config;
	private $limit;

	function SearchLogic(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->config = CustomSearchFieldUtil::getConfig();
	}

	function get(){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$sql = self::buildQuery();
		$binds = self::buildBinds();
		
		try{
			$res = $itemDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();
		
		$items = array();
		foreach($res as $v){
			$items[] = $itemDao->getObject($v);
		}
		
		return $items;
	}
	
	private function buildQuery(){
		$sql = "SELECT i.* from soyshop_item i ".
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
				
		return $sql;
	}
	
	private function buildBinds(){
		return array();
	}
	
	function setFieldId($fieldId){
		$this->fieldId = $fieldId;
	}
	
	function setLimit($limit){
		$this->limit = $limit;
	}
}
?>