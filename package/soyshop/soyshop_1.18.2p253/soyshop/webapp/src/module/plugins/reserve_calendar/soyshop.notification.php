<?php
class ReserveCalendarOrderNotification extends SOYShopNotification{

	function execute(){
		if(!isset($_GET["token"]) || !strlen(trim($_GET["token"]))){
			header("Location:" . soyshop_get_cart_url(false, true));
		}

		$token = trim($_GET["token"]);
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
		$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");

		try{
			$orderId = $dao->getByToken($token)->getOrderId();
		}catch(Exception $e){
			$orderId = null;
		}

		if(is_null($orderId)){
			echo "既に本登録済みです";
			exit;
		}

		$now = time();
		$sql = "UPDATE soyshop_reserve_calendar_reserve SET token='', temp=0, temp_date=NULL, reserve_date=" . $now . " WHERE order_id = :orderId";
		try{
			$dao->executeUpdateQuery($sql, array(":orderId" => $orderId));
		}catch(Exception $e){
			var_dump($e);
		}

		//注文の方も仮登録を解除
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$order = $orderDao->getById($orderId);
			$order->setStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
			$orderDao->update($order);
		}catch(Exception $e){
			var_dump($e);
		}

		echo "本登録に変更しました";
		exit;
	}
}
SOYShopPlugin::extension("soyshop.notification", "reserve_calendar", "ReserveCalendarOrderNotification");
