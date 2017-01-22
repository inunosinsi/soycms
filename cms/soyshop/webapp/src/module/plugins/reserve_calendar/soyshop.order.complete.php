<?php
class ReserveCalendarOrderComplete extends SOYShopOrderComplete{

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){
		
		/** @ToDo 他のショップサイトの顧客情報に登録する **/
	}

}

SOYShopPlugin::extension("soyshop.order.complete", "reserve_calendar", "ReserveCalendarOrderComplete");
?>