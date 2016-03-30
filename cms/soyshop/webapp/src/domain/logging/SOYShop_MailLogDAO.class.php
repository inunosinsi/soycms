<?php
/**
 * @entity logging.SOYShop_MailLog
 */
abstract class SOYShop_MailLogDAO extends SOY2DAO{

	/**
	 * @trigger onInsert
	 * @return id
	 */
	abstract function insert(SOYShop_MailLog $bean);

	abstract function update(SOYShop_MailLog $bean);

	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 * @query #id# = :id AND #userId# = :userId
	 */
	abstract function getByIdAndUserId($id, $userId);
	
	/**
	 * @return list
	 * @order send_date DESC
	 */
	abstract function getByOrderId($orderId);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":sendDate"] = time();
		
		if($binds[":isSuccess"] != SOYShop_MailLog::SUCCESS){
			$binds[":isSuccess"] = SOYShop_MailLog::FAILED;
		}
		return array($query, $binds);
	}
}
?>