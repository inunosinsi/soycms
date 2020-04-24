<?php
/**
 * @entity SOYShop_Ticket
 */
abstract class SOYShop_TicketDAO extends SOY2DAO{
   	/**
	 * @return id
	 * @trigger onUpdate
	 */
   	abstract function insert(SOYShop_Ticket $bean);

	/**
	 * @query user_id = :userId
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Ticket $bean);

	/**
	 * @return object
	 */
	abstract function getByUserId($userId);

	abstract function deleteByUserId($userId);

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
