<?php

class ReserveLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
	}

	function getReservedSchedules($time = null, $limit = 16){
		if(is_null($time)) $time = time();

		return self::dao()->getReservedSchedules($time, $limit);
	}

	function getReservedListByScheduleId($scheduleId, $isTmp = false){
		return self::dao()->getReservedListByScheduleId($scheduleId, $isTmp);
	}

	function getReservedCountByScheduleId($scheduleId, $isTmp = false){
		return self::dao()->getReservedCountByScheduleId($scheduleId, $isTmp);
	}

	function getReservedSchedulesByPeriod($year = null, $month = null, $isTmp = false){
		//どちらかが指定されていない時は動きません
		if(is_null($year) || is_null($month)) return array();

		//schedule_idと予約数を返す
		return self::dao()->getReservedSchedulesByPeriod($year, $month, $isTmp);
	}

	//予約済みのスケジュールオブジェクトを取得する
	function getReservedScheduleListByUserIdAndPeriod($userId, $year = null, $month = null, $isTmp = false){
		//どちらかが指定されていない時は動きません
		if(is_null($year) || is_null($month)) return array();

		//schedule_idと予約数を返す
		return self::dao()->getReservedScheduleListByUserIdAndPeriod($userId, $year, $month, $isTmp);
	}

	//指定の日から○日分の予定を取得する
	function getReservedCountListFromDaysByItemId($itemId, $now=null, $days=30){
		if(is_null($now)) $now = time();
		$now = soyshop_shape_timestamp($now);	//整形
		return self::dao()->getReservedCountListFromDaysByItemId($itemId, $now, $days);
	}

	function checkIsUnsoldSeatByScheduleId($scheduleId){
		//boolean
		return self::dao()->checkIsUnsoldSeatByScheduleId($scheduleId);
	}

	//管理画面で本登録
	function registration($reserveId){
		$resDao = self::dao();
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
			SOY2DAOFactory::create("order.SOYShop_OrderDAO")->update($order);
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

	function getTokensByOrderId($orderId){
		return self::dao()->getTokensByOrderId($orderId);
	}

	/** マイページ **/

	//ページを開いているユーザの予約であるか調べる
	function checkReserveByUserId($reserveId, $userId){
		return self::dao()->checkReserveByUserId($reserveId, $userId);
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
		return $dao;
	}
}
