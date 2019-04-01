<?php
class ReserveCalendarOrderComplete extends SOYShopOrderComplete{

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){
		//仮登録の場合、注文を仮登録の状態にする
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$config = ReserveCalendarUtil::getConfig();
		if(isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::IS_TMP){
			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$order->setStatus(SOYShop_Order::ORDER_STATUS_INTERIM);
			try{
				$dao->update($order);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

}

SOYShopPlugin::extension("soyshop.order.complete", "reserve_calendar", "ReserveCalendarOrderComplete");
