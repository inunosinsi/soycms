<?php

class SearchLogic extends SOY2LogicBase{

	private $fieldId;
	private $config;
	private $limit;
	private $itemDao;
	
	private $where = array();
	private $binds = array();

	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->config = CustomSearchFieldUtil::getConfig();
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}

	function get(){
		self::register();
		
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
		$sql = "SELECT i.* from soyshop_item i ".
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ".
				"WHERE i.is_disabled != " . SOYShop_Item::IS_DISABLED . " ";
				
		foreach($this->where as $where){
			$sql .= " AND " . $where;
		}
		
		$sql .= " Limit " . $this->limit;
				
		return $sql;
	}
	
	function setCondition($conditions){
		if(count($conditions)) foreach($conditions as $key => $value){
			switch($key){
				case $this->fieldId:
					switch($this->config[$this->fieldId]["type"]){
						case CustomSearchFieldUtil::TYPE_CHECKBOX:
							foreach($value as $i => $v){
								$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId . $i;
								$this->binds[":" . $this->fieldId . $i] = "%" . trim($v) . "%";
							}
							
							break;
						default:
							$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId;
							$this->binds[":" . $this->fieldId] = "%" . trim($value) . "%";
					}
					break;
				case "nothing":
					$this->where[] = "s." . $this->fieldId . " IS NULL";
					break;
				case "item_is_open":
					if(count($value)){
						$this->where[] = $key . " IN (" . implode(",", $value) . ") ";
					}
					break;
				default:
					$this->where[] = "i." . $key . " LIKE :" . $key;
					$this->binds[":" . $key] = "%" . trim($value) . "%";
			}
		}
	}
	
	private function register(){
		try{
			$res = $this->itemDao->executeQuery("SELECT item_id FROM soyshop_custom_search ORDER BY item_id DESC LIMIT 1;", array());
		}catch(Exception $e){
			return;
		}
		
		if(!isset($res[0]["item_id"])) return;
		
		$lastId = (int)$res[0]["item_id"];
		
		try{
			$res = $this->itemDao->executeQuery("SELECT id FROM soyshop_item WHERE id > :id;", array(":id" => $lastId));
		}catch(Exception $e){
			return;
		}
		
		if(!count($res)) return;
		
		foreach($res as $v){
			try{
				$this->itemDao->executeQuery("INSERT INTO soyshop_custom_search (item_id) VALUES (:id)", array(":id" => $v["id"]));
			}catch(Exception $e){
				//
			}
		}
	}
	
	function setFieldId($fieldId){
		$this->fieldId = $fieldId;
	}
	
	function setLimit($limit){
		$this->limit = $limit;
	}
}
?>