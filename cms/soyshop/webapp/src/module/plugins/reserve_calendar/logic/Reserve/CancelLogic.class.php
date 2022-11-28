<?php

class CancelLogic extends SOY2LogicBase{

	function __construct(){}

	function cancel(int $reserveId){
		$resDao = soyshop_get_hash_table_dao("reserve_calendar");
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

		$canDao = soyshop_get_hash_table_dao("reserve_cancel");
		$cancel = new SOYShopReserveCalendar_Cancel();
		$cancel->setScheduleId($reserve->getScheduleId());
		$cancel->setOrderId($reserve->getOrderId());

		try{
			$canDao->insert($cancel);
		}catch(Exception $e){
			return false;
		}

		//予約に紐付いた注文をキャンセルにする
		$order = soyshop_get_order_object($reserve->getOrderId());		
		if($order->getStatus() != SOYShop_Order::ORDER_STATUS_CANCELED){
			$order->setStatus(SOYShop_Order::ORDER_STATUS_CANCELED);
			try{
				soyshop_get_hash_table_dao("order")->update($order);
			}catch(Exception $e){
				//
			}
		}

		$resDao->commit();
		return true;
	}

	function getCancelListByScheduleId(int $scheduleId){
		return soyshop_get_hash_table_dao("reserve_cancel")->getCancelListByScheduleId($scheduleId);
	}
}
