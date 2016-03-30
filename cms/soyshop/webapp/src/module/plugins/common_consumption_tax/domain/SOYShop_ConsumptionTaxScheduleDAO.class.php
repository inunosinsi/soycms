<?php

/**
 * @entity SOYShop_ConsumptionTaxSchedule
 */
abstract class SOYShop_ConsumptionTaxScheduleDAO extends SOY2DAO{
	
	/**
	 * @return list
	 * @order start_date DESC
	 */
	abstract function get();
	
	/**
	 * @return list
	 * @query start_date <= :date
	 * @order start_date DESC
	 */
	abstract function getScheduleByDate($date);

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_ConsumptionTaxSchedule $bean);
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_ConsumptionTaxSchedule $bean);
	
	abstract function deleteById($id);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		return array($query, $binds);
	}
}
?>