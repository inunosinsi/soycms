<?php

class ReserveCalendarItemOrder extends SOYShopItemOrderBase{

	function edit(SOYShop_ItemOrder $itemOrder){
		//itemorderに合わせて残席数を変更
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
		$resDao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
		try{
			$res = $resDao->getByOrderId($itemOrder->getOrderId());
		}catch(Exception $e){
			var_dump($e);
			return;
		}

		$res->setSeat($itemOrder->getItemCount());
		try{
			$resDao->update($res);
		}catch(Exception $e){
			var_dump($e);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.order", "reserve_calendar", "ReserveCalendarItemOrder");
