<?php

class ReturnsSlipNumberUpdate extends SOYShopOrderStatusUpdate{

	function execute(SOYShop_Order $order){
		if(isset($_SERVER["PATH_INFO"]) && is_numeric(strpos($_SERVER["PATH_INFO"], "Order/Mail/"))){
			SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
			if((int)$order->getStatus() === ReturnsSlipNumberUtil::STATUS_CODE){
				/**
				$sql = "SELECT order_id, order_date FROM soyshop_order_state_history WHERE order_id IN (" . implode(",", $orderIds) . ") AND content LIKE '%<strong>「返却済み」</strong>%' ORDER BY order_date ASC;";
				try{
					$res = self::soyshop_get_hash_table_dao("order_state_history")->executeQuery($sql);
				}catch(Exception $e){
					$res = array();
				}
				**/

				SOY2Logic::createInstance("logic.order.OrderLogic")->addHistory(
					$order->getId(),
					"注文状態を<strong>「返却済み」</strong>に変更しました。"
				);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.status.update", "returns_slip_number", "ReturnsSlipNumberUpdate");
