<?php

class CancelLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Reserve");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Cancel");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_CancelDAO");
	}

	function cancel($reserveId){
		$resDao = self::dao();
		try{
			$reserve = $resDao->getById($reserveId);
		}catch(Exception $e){
			return;
		}


		$resDao->begin();

		try{
			$resDao->deleteById($reserveId);
		}catch(Exception $e){
			return;
		}

		$canDao = SOY2DAOFactory::create("SOYShopReserveCalendar_CancelDAO");
		$cancel = new SOYShopReserveCalendar_Cancel();
		$cancel->setScheduleId($reserve->getScheduleId());
		$cancel->setOrderId($reserve->getOrderId());

		try{
			$canDao->insert($cancel);
		}catch(Exception $e){
			var_dump($e);
			return;
		}

		$resDao->commit();
	}

	function getCancelListByScheduleId($scheduleId){
		return SOY2DAOFactory::create("SOYShopReserveCalendar_CancelDAO")->getCancelListByScheduleId($scheduleId);
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
		return $dao;
	}
}
