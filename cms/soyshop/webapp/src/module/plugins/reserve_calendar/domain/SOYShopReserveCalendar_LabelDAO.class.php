<?php
/**
 * @entity SOYShopReserveCalendar_Label
 */
abstract class SOYShopReserveCalendar_LabelDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShopReserveCalendar_Label $bean);

	/**
	 * @trigger onInsert
	 */
	abstract function update(SOYShopReserveCalendar_Label $bean);

	/**
	 * @return list
	 * @order display_order ASC
	 */
	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return list
	 * @order display_order ASC
	 */
	abstract function getByItemId($itemId);

	abstract function deleteById($id);

	function registerdItemIdsOnLabel(){
		$sql = "SELECT DISTINCT item_id FROM soyshop_reserve_calendar_label ";

		try{
			$res = $this->executeQuery($sql, array());
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $v["item_id"];
		}

		return $list;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":displayOrder"]) || !strlen($binds[":displayOrder"])) $binds[":displayOrder"] = 127;

		return array($query, $binds);
	}
}
