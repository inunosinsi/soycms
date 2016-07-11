<?php

class SearchLogic extends SOY2LogicBase{
	
	private $itemDao;
	
	private $limit;
	
	private $where = array();
	private $binds = array();
	
	function SearchLogic(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	function get(){
		
		$sql = self::buildQuery();
		
		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
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
		
		foreach($this->where as $where){
			$sql .= " AND " . $where;
		}

		$sql .= " LIMIT " . $this->limit;
				
		return $sql;
	}
	
	function setCondition($conditions){
		foreach($conditions as $key => $value){
			switch($key){
//				case $this->fieldId:
//					switch($this->config[$this->fieldId]["type"]){
//						case CustomSearchFieldUtil::TYPE_CHECKBOX:
//							foreach($value as $i => $v){
//								$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId . $i;
//								$this->binds[":" . $this->fieldId . $i] = "%" . trim($v) . "%";
//							}
//							
//							break;
//						default:
//							$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId;
//							$this->binds[":" . $this->fieldId] = "%" . trim($value) . "%";
//					}
//					break;
//				case "nothing":
//					$this->where[] = "s." . $this->fieldId . " IS NULL";
//					break;
				default:
					$this->where[] = "" . $key . " LIKE :" . $key;
					$this->binds[":" . $key] = "%" . trim($value) . "%";
			}
		}
	}
	
	function setLimit($limit){
		$this->limit = $limit;
	}
}
?>