<?php

class DataBaseLogic extends SOY2LogicBase{
	
	function DataBaseLogic(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
	}
	
	function addColumn($key, $type){
		if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return false;
		
		$sql = "ALTER TABLE soyshop_custom_search ADD COLUMN " . $key . " ";
		
		switch($type){
			case CustomSearchFieldUtil::TYPE_INTEGER:
			case CustomSearchFieldUtil::TYPE_RANGE:
				$sql .= "INTEGER";
				break;
			case CustomSearchFieldUtil::TYPE_TEXTAREA:
			case CustomSearchFieldUtil::TYPE_RICHTEXT:
				$sql .= "TEXT";
				break;
			default:
				$sql .= "VARCHAR(255)";
		}
		
		$dao = new SOY2DAO();
				
		try{
			$dao->executeUpdateQuery($sql, array());
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	function deleteColumn($key){
		if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return;
				
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("ALTER TABLE soyshop_custom_search DROP COLUMN " . $key, array());
		}catch(Exception $e){
			//SQLiteではカラムを削除できない
		}
	}
	
	/**
	 * @params itemId integer, values array(array("field_id" => string))
	 */
	function save($itemId, $values){
		
		$sets = array();
		
		foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			if(!isset($values[$key])) {
				$sets[$key] = null;
				continue;
			}
			
			switch($field["type"]){
				case CustomSearchFieldUtil::TYPE_INTEGER:
				case CustomSearchFieldUtil::TYPE_RANGE:
					$sets[$key] = (is_numeric($values[$key])) ? (int)$values[$key] : null;
					break;
				case CustomSearchFieldUtil::TYPE_CHECKBOX:
					$sets[$key] = (count($values[$key])) ? implode(",", $values[$key]) : null;
					break;
				default:
					$sets[$key] = (strlen($values[$key])) ? $values[$key] : null;
			}
		}
		
		$this->insert($itemId, $sets);
	}
	
	function insert($itemId, $sets){		
		$columns = array();
		$values = array();
		$binds = array();
		
		$columns[] = "item_id";
		$values[] = (int)$itemId;
		
		foreach($sets as $key => $value){
			$columns[] = $key;
			$values[] = ":" . $key;
			$binds[":" . $key] = $value;
		}
		
		$sql = "INSERT INTO soyshop_custom_search ".
				"(" . implode(",", $columns) . ") ".
				"VALUES (" . implode(",", $values) . ")";
		
		$dao = new SOY2DAO();
		
		try{
			$dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$this->update($itemId, $columns, $values, $binds);
		}
	}
	
	function update($itemId, $columns, $values, $binds){
		$sql = "UPDATE soyshop_custom_search SET ";
		$first = true;
		foreach($columns as $i => $column){
			if($column == "item_id") continue;
			if(!$first) $sql .= ", ";
			$first = false;
			$sql .= $column . " = " . $values[$i];
		}
		$sql .= " WHERE item_id = " . $itemId;
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery($sql, $binds);
		}catch(Exception $e){
			//
		}
	}
	
	function delete($itemId){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("DELETE FROM soyshop_custom_search WHERE item_id = :item_id", array(":item_id" => $itemId));
		}catch(Exception $e){
			//
		}
	}
	
	
	function migrate(){
		$dao = new SOY2DAO();
		
		//すべての商品番号を取得する
		try{
			$results = $dao->executeQuery("SELECT id FROM soyshop_item");
		}catch(Exception $e){
			$results = array();
		}
		
		if(!count($results)) return false;
		$attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$configs = SOYShop_ItemAttributeConfig::load(true);
		$keys = array_keys($configs);
		
		$keysTmp = array();
		foreach($keys as $key){
			if($configs[$key]->getType() == "image" || $configs[$key]->getType() == "file" || $configs[$key]->getType() == "link"){
				//何もしない
			}else{
				$keysTmp[] = $key;
			}
		}
		$keys = $keysTmp;
		unset($configs);
		unset($keysTmp);
		
		if(!count($keys)) return false;
		
		//カスタムサーチフィールドの方のフィールドID
		$skeys = array_keys(CustomSearchFieldUtil::getConfig());
		
		foreach($results as $v){
			if(isset($v["id"]) && is_numeric($v["id"])){
				$itemId = (int)$v["id"];
				try{
					$attrs = $attrDao->getByItemId($itemId);
				}catch(Exception $e){
					continue;
				}
				
				if(!count($attrs)) continue;
				
				
				$sets = array();
				foreach($attrs as $key => $attr){
					if(array_search($key, $skeys) === false) continue;	//0を含むためにfalseにした
					if(array_search($key, $keys) !== false && strlen($attr->getValue())) $sets[$key] = $attr->getValue();	
				}
												
				$this->insert($itemId, $sets);
			}
		}
				
		return true;
	}
	
	function getByItemId($itemId){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT * FROM soyshop_custom_search WHERE item_id = :item_id LIMIT 1", array(":item_id" => $itemId));
		}catch(Exception $e){
			return array();
		}
		
		return (isset($res[0])) ? $res[0] : array();
	}
}
?>