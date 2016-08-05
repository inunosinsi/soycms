<?php
/**
 * @entity sample.SOYMock_Sample
 * 
 * 使い方
 * $dao = SOY2DAOFactory::create("sample.SOYMock_SampleDAO");
 * $array = $dao->get();
 */
abstract class SOYMock_SampleDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYMock_Sample $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYMock_Sample $bean);
	
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
	 * @return object
	 */
	abstract function getByName($name);
	
	/**
	 * @return list
	 * @query name = :name AND create_date > :createDate
	 * @order id desc
	 */
	abstract function getByNameAndCreateDate($name, $createDate);
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function count();
	
	//処理を自由に書きたい場合はabstractを外す
	function getSamples($str){
		
		$dao = new SOY2DAO();
		
		$sql = "SELECT * FROM soymock_sample WHERE name LIKE %%:name%%";
		$binds = array(":name" => $str);
		
		try{
			$results = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$results = array();
		}
		
		return $results;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
?>