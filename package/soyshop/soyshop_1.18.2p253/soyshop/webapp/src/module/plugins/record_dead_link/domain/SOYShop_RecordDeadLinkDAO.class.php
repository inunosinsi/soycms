<?php
/**
 * @entity SOYShop_RecordDeadLink
 */
abstract class SOYShop_RecordDeadLinkDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_RecordDeadLink $bean);
	
	/**
	 * @return list
	 * @order register_date DESC
	 */
	abstract function get();
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		
		$binds[":registerDate"] = time();
		
		return array($query, $binds);
	}
}
?>