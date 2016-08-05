<?php
/**
 * @entity SOYList_Item
 */
abstract class SOYList_ItemDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYList_Item $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYList_Item $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 */
	abstract function getByCategory($category);
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function count();
	
	function getItemsByIds($ids){
		$sql = "SELECT * ".
				"FROM soylist_item ".
				"WHERE id IN (".implode(",",$ids).") ".
				"ORDER BY sort ASC, id ASC";
		try{
			$results = $this->executeQuery($sql,array());
		}catch(Exception $e){
			return array();
		}
		
		$items = array();
		if(count($results) > 0){
			foreach($results as $result){
				$result["createDate"] = $result["create_date"];
				$result["updateDate"] = $result["update_date"];
				$items[] = SOY2::cast("SOYList_Item",$result);
			}
		}
		return $items;
	}
	
	function getByCategoryIdAndChecked($categoryId,$ids){
		$sql = "SELECT * ".
				"FROM soylist_item ".
				"WHERE id IN (".implode(",",$ids).") ".
				"AND category = :category ".
				"ORDER BY sort ASC, id ASC";
				
		$binds = array(":category"=>$categoryId);
		try{
			$results = $this->executeQuery($sql,$binds);
		}catch(Exception $e){
			return array();
		}
		
		$items = array();
		if(count($results) > 0){
			foreach($results as $result){
				$result["createDate"] = $result["create_date"];
				$result["updateDate"] = $result["update_date"];
				$items[] = SOY2::cast("SOYList_Item",$result);
			}
		}
		return $items;
	}
	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
		$binds[":sort"] = 100000;
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query,$binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		$binds[":updateDate"] = time();
		return array($query,$binds);
	}
}
?>