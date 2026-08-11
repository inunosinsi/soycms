<?php

class ReserveLogic extends SOY2LogicBase{

	function __construct(){}

	function getReservedSchedules(int $time=0, int $limit=16){
		if($time === 0) $time = time();
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedSchedules($time, $limit);
	}

	function getReservedListByScheduleId(int $scheduleId, bool $isTmp=false){
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedListByScheduleId($scheduleId, $isTmp);
	}

	function getReservedCountByScheduleId(int $scheduleId, bool $isTmp=false){
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedCountByScheduleId($scheduleId, $isTmp);
	}

	function getReservedSchedulesByPeriod($year=null, $month=null, $isTmp=false){
		//どちらかが指定されていない時は動きません
		if(is_null($year) || is_null($month)) return array();

		//schedule_idと予約数を返す
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedSchedulesByPeriod($year, $month, $isTmp);
	}

	//予約済みのスケジュールオブジェクトを取得する
	function getReservedScheduleListByUserIdAndPeriod($userId, $year = null, $month = null, $isTmp = false){
		//どちらかが指定されていない時は動きません
		if(is_null($year) || is_null($month)) return array();

		//schedule_idと予約数を返す
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedScheduleListByUserIdAndPeriod($userId, $year, $month, $isTmp);
	}

	//指定の日から○日分の予定を取得する
	function getReservedCountListFromDaysByItemId(int $itemId, int $now=0, int $days=30, bool $isTmp=false){
		if($now === 0) $now = time();
		$now = soyshop_shape_timestamp($now);	//整形
		return soyshop_get_hash_table_dao("reserve_calendar")->getReservedCountListFromDaysByItemId($itemId, $now, $days, $isTmp);
	}

	function checkIsUnsoldSeatByScheduleId(int $scheduleId){
		//boolean
		return soyshop_get_hash_table_dao("reserve_calendar")->checkIsUnsoldSeatByScheduleId($scheduleId);
	}

	//管理画面で本登録
	function registration(int $reserveId){
		$resDao = soyshop_get_hash_table_dao("reserve_calendar");
		try{
			$reserve = $resDao->getById($reserveId);
		}catch(Exception $e){
			error_log($e, 0);
			return false;
		}

		$resDao->begin();
		$reserve->setToken(null);
		$reserve->setTemp(SOYShopReserveCalendar_Reserve::NO_TEMP);
		$reserve->setTempDate(null);
		$reserve->setReserveDate(time());

		try{
			$resDao->update($reserve);
		}catch(Exception $e){
			$resDao->rollback();
			error_log($e, 0);
			return false;
		}

		//注文状態を受付中にする
		$order = soyshop_get_order_object($reserve->getorderId());
		$order->setStatus(SOYShop_Order::ORDER_STATUS_RECEIVED);
		try{
			soyshop_get_hash_table_dao("order")->update($order);
		}catch(Exception $e){
			$resDao->rollback();
			error_log($e, 0);
			return false;
		}

		$resDao->commit();

		SOYShopPlugin::load("soyshop.order.status.update");
		SOYShopPlugin::invoke("soyshop.order.status.update", array(
			"order" => $order,
			"mode" => "reserve"
		));

		return true;
	}

	function getTokensByOrderId(int $orderId){
		return soyshop_get_hash_table_dao("reserve_calendar")->getTokensByOrderId($orderId);
	}

	/** マイページ **/

	//ページを開いているユーザの予約であるか調べる
	function checkReserveByUserId(int $reserveId, int $userId){
		return soyshop_get_hash_table_dao("reserve_calendar")->checkReserveByUserId($reserveId, $userId);
	}
}
