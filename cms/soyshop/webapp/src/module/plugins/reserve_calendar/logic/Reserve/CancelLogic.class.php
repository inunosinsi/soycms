<?php

class CancelLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_CancelDAO");
	}

	function cancel($reserveId){
		$resDao = self::dao();
		try{
			$reserve = $resDao->getById($reserveId);
		}catch(Exception $e){
			return false;
		}


		$resDao->begin();

		try{
			$resDao->deleteById($reserveId);
		}catch(Exception $e){
			return false;
		}

		$canDao = SOY2DAOFactory::create("SOYShopReserveCalendar_CancelDAO");
		$cancel = new SOYShopReserveCalendar_Cancel();
		$cancel->setScheduleId($reserve->getScheduleId());
		$cancel->setOrderId($reserve->getOrderId());

		try{
			$canDao->insert($cancel);
		}catch(Exception $e){
			return false;
		}

		//予約に紐付いた注文をキャンセルにする
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$order = $orderDao->getById($reserve->getOrderId());
		}catch(Exception $e){
			//
		}

		if($order->getStatus() != SOYShop_Order::ORDER_STATUS_CANCELED){
			$order->setStatus(SOYShop_Order::ORDER_STATUS_CANCELED);
			try{
				$orderDao->update($order);
			}catch(Exception $e){
				//
			}
		}

		$resDao->commit();
		return true;
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
