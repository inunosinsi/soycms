<?php

class SearchLogic extends SOY2LogicBase{
	
	private $itemDao;
	
	private $where = array();
	private $binds = array();
	
	function SearchLogic(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	function get(){
		
		$sql = self::buildQuery();
		
		try{
			$res = $this->itemDao->executeQuery($sql, array());
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();
		
		$items = array();
		foreach($res as $v){
			$items[] = $this->itemDao->getObject($v);
		}
		
		return $items;
	}
	
	private function buildQuery(){
		$sql = "SELECT * FROM soyshop_item ".
				"WHERE item_type IN (\"".SOYShop_Item::TYPE_SINGLE."\",\"".SOYShop_Item::TYPE_GROUP."\") ".
				"AND is_disabled != " . SOYShop_Item::IS_DISABLED . " ";
				
		return $sql;
	}
}
?>