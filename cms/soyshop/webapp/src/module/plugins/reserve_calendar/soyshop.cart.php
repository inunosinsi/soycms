<?php

class ReserveCalendarCart extends SOYShopCartBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

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

	function afterOperation(CartLogic $cart){
		$items = $cart->getItems();
		if(count($items)){
			//商品は必ず一つモードの場合は前に入れた商品は削除する
			SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
			$config = ReserveCalendarUtil::getConfig();
			if(isset($config["only"]) && (int)$config["only"] === ReserveCalendarUtil::IS_ONLY){
				//既に商品が入っていれば、indexが1の商品をindexが0にする
				if(count($items) > 1){
					$indexOne = ReserveCalendarUtil::getCartAttributeId("schedule_id", 1, $items[1]->getItemId());
					$schId = $cart->getAttribute($indexOne);
					$cart->clearAttribute($indexOne);
					$cart->setAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", 0, $items[0]->getItemId()), $schId);

					$item = end($items);
					$item->setItemCount(1);	// @ToDo 要設定項目？
					$cart->setItems(array($item));
				}else{	//予定が一つしか入っていない場合、同一予定であれば個数を1にする
					$item = array_shift($items);
					$item->setItemCount(1);	// @ToDo 要設定項目？
					$cart->setItems(array($item));
				}

				//大人と子供の人数をセッションに入れる
				$idx = key($items);
				if(isset($_POST["Option"]["adult"]) && isset($_POST["Option"]["child"])){
					$cart->setAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_adult", $idx, $items[$idx]->getItemId()), (int)$_POST["Option"]["adult"]);
					$cart->setAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_child", $idx, $items[$idx]->getItemId()), (int)$_POST["Option"]["child"]);
				}
			}

			$schLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");

			//価格の更新
			foreach($items as $index => $itemOrder){
				$schId = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $index, $itemOrder->getItemId()));
				$schPrice = (int)$schLogic->getScheduleById($schId)->getPrice();
				$itemOrder->setItemPrice($schPrice);
				$itemOrder->setTotalPrice($schPrice * $itemOrder->getItemCount());
			}
		}
	}

	function displayPage01(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage02(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage03(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage04(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage05(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	//古い仮登録注文を無効注文(STATUS_INVALID=0)に変更する
	private function _changeStatusOlderOrder(){
		if(SOYShopPluginUtil::checkIsActive("change_order_status_invalid")){
			SOY2::import("module.plugins.change_order_status_invalid.util.ChangeOrderStatusInvalidUtil");
			ChangeOrderStatusInvalidUtil::changeInvalidStatusOlderOrder();
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "reserve_calendar", "ReserveCalendarCart");
