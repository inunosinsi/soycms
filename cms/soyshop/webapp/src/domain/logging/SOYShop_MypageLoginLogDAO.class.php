<?php
/**
 * @entity logging.SOYShop_MypageLoginLog
 */
abstract class SOYShop_MypageLoginLogDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_MypageLoginLog $bean);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":logDate"] = time();
		return array($query, $binds);
	}
}
