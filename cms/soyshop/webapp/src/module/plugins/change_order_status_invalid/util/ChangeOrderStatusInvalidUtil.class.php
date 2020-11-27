<?php

class ChangeOrderStatusInvalidUtil {

	//古い仮登録注文を無効注文(STATUS_INVALID=0)に変更する
	public static function changeInvalidStatusOlderOrder(){
		$cnf = self::_config();
		$min = (isset($cnf["minute"]) && is_numeric($cnf["minute"])) ? (int)$cnf["minute"] : 5;

		$timming = time() - ($min / 60) * 1 * 60 * 60;	//$min分前

		try{
			$results = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->executeQuery("SELECT id FROM soyshop_order WHERE order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " AND order_date < " . $timming);
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) return;

		// 在庫を戻す
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		foreach($results as $v){
			if(!isset($v["id"]) || !is_numeric($v["id"])) continue;
			$orderLogic->changeOrderStatus(array($v["id"]), SOYShop_Order::ORDER_STATUS_INVALID);
		}
	}

	public static function getConfig(){
		return self::_config();
	}

	public static function saveConfig($values){
		$values["minute"] = (isset($values["minute"]) && is_numeric($values["minute"])) ? (int)$values["minute"] : 5;
		SOYShop_DataSets::put("change_order_status_invalid.config", $values);
	}

	private static function _config(){
		return SOYShop_DataSets::get("change_order_status_invalid.config", array(
			"minute" => 5
		));
	}
}
