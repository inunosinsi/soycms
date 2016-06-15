<?php
/**
 * @entity SOYShop_Campaign
 */
abstract class SOYShop_CampaignDAO extends SOY2DAO {
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_Campaign $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Campaign $bean);
	
	/**
	 * @return list
	 * @query is_disabled != 1
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(strlen($binds[":postPeriodStart"])) $binds[":postPeriodStart"] = 0;
		if(!strlen($binds[":postPeriodEnd"])) $binds[":postPeriodEnd"] = 2147483647;
		
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!strlen($binds[":postPeriodStart"])) $binds[":postPeriodStart"] = 0;
		if(!strlen($binds[":postPeriodEnd"])) $binds[":postPeriodEnd"] = 2147483647;
		
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
}
?>