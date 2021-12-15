<?php
class ReserveCalendarOrderComplete extends SOYShopOrderComplete{

	function beforeComplete(CartLogic $cart){
		$items = $cart->getItems();
		if(count($items)){
			foreach($items as $idx => $item){
				//大人、子供の人数を入力させている場合
				SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
				$adultSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_adult", $idx, $item->getItemId()));
				if(isset($adultSeat) && is_numeric($adultSeat) && $adultSeat > 0){
					$childSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_child", $idx, $item->getItemId()));
					if(!is_numeric($childSeat)) $childSeat = 0;
					$cart->setOrderAttribute("reserve_manager_composition_" . $idx, "予約構成", "大人：" . $adultSeat . "人 子供：" . $childSeat . "人");
				}
			}
			$cart->save();
		}
	}

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){
		//仮登録の場合、注文を仮登録の状態にする
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$cnf = ReserveCalendarUtil::getConfig();
		if(isset($cnf["tmp"]) && $cnf["tmp"] == ReserveCalendarUtil::IS_TMP){
			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$order->setStatus(SOYShop_Order::ORDER_STATUS_INTERIM);
			try{
				$dao->update($order);
			}catch(Exception $e){
				//var_dump($e);
			}
		}
	}

}

SOYShopPlugin::extension("soyshop.order.complete", "reserve_calendar", "ReserveCalendarOrderComplete");
