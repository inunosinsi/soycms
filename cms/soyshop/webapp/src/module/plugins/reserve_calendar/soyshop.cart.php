<?php

class ReserveCalendarCart extends SOYShopCartBase{

	function doOperation(){

		//
		if(isset($_REQUEST["a"]) && $_REQUEST["a"] == "add"){

			if(isset($_REQUEST["schId"]) && is_numeric($_REQUEST["schId"])){
				SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");

				//スケジュールが登録されているか確認してから商品IDを$_REQUESTに渡す
				try{
					$sch = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO")->getById($_REQUEST["schId"]);
				}catch(Exception $e){
					$sch = new SOYShopReserveCalendar_Schedule();
				}

				if(!is_null($sch->getItemId())){
					//残席数の確認もしておきたい
					if(SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->checkIsUnsoldSeatByScheduleId($sch->getId())){
						$_REQUEST["item"] = $sch->getItemId();
						$_REQUEST["item_option"]["schedule_id"] = $sch->getId();	//商品オプションの拡張ポイントを起動させるための処理
					}
				}
			}

			/** @ToDo エラーの場合はどうしよう？ **/
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "reserve_calendar", "ReserveCalendarCart");
