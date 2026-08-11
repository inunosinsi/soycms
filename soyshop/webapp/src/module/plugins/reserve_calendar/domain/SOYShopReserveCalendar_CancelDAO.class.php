<?php
SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Cancel");

/**
 * @entity SOYShopReserveCalendar_Cancel
 */
abstract class SOYShopReserveCalendar_CancelDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShopReserveCalendar_Cancel $bean);

	abstract function deleteById($id);

	function getCancelListByScheduleId($scheduleId){
		$sql = "SELECT can.cancel_date, can.order_id, o.user_id, u.name AS user_name, u.mail_address, u.telephone_number FROM soyshop_reserve_calendar_cancel can ".
				"INNER JOIN soyshop_order o ".
				"ON can.order_id = o.id ".
				"INNER JOIN soyshop_user u ".
				"ON o.user_id = u.id ".
				"WHERE can.schedule_id = :schId ".
				"ORDER BY can.cancel_date ASC ";

		try{
			$res = $this->executeQuery($sql, array(":schId" => $scheduleId));
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		return $res;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":cancelDate"] = time();

		return array($query, $binds);
	}
}
