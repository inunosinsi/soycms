<?php

class ReserveCalendarItemOrder extends SOYShopItemOrderBase{

	function edit(SOYShop_ItemOrder $itemOrder){
		//itemorderに合わせて残席数を変更
		$resDao = soyshop_get_hash_table_dao("reserve_calendar");
		try{
			$reserves = $resDao->getByOrderId($itemOrder->getOrderId());
		}catch(Exception $e){
			$reserves = array();
		}

		if(!count($reserves)) return;

		// @ToDo 複数個の注文の場合を検討
		$res = $reserves[0];
		$res->setSeat($itemOrder->getItemCount());
		try{
			$resDao->update($res);
		}catch(Exception $e){
			//var_dump($e);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.order", "reserve_calendar", "ReserveCalendarItemOrder");
