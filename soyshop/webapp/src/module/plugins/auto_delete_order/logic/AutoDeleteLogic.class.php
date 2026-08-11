<?php
SOY2::import("domain.order.SOYShop_Order");

class AutoDeleteLogic extends SOY2LogicBase {

	const MODE_INVALID = SOYShop_Order::ORDER_STATUS_INVALID;	//無効注文
	const MODE_CANCEL = SOYShop_Order::ORDER_STATUS_CANCELED;	//キャンセル注文
	const MODE_INTERIM = SOYShop_Order::ORDER_STATUS_INTERIM;	//仮登録注文

	function __construct(){
		SOY2::import("module.plugins.auto_delete_order.util.AutoDeleteOrderUtil");
	}

	function execute(){
		$conf = AutoDeleteOrderUtil::getConfig();

		//自動キャンセル
		if(isset($conf["auto_cancel"]) && $conf["auto_cancel"] == 1){
			$timming = time() - (int)$conf["auto_cancel_timming"] * 31 * 24 * 60 * 60;
			try{
				self::dao()->executeUpdateQuery("UPDATE soyshop_order SET order_status = " . self::MODE_CANCEL . " WHERE order_date < " . $timming);
			}catch(Exception $e){
				//
			}
		}

		$orderIds = array();

		foreach(AutoDeleteOrderUtil::getTypes() as $t){
			if(!isset($conf[$t]) || (int)$conf[$t] !== 1 || strpos($t, "auto") === 0) continue;	//自動キャンセルはここでは行わない
			switch($t){
				case "invalid":
					$mode = self::MODE_INVALID:
					break;
				case "pre":
					$mode = self::MODE_INTERIM;
					break;
				case "cancel":
				default:
					$mode = self::MODE_CANCEL;
					break;
			}
			$timming = time() - (int)$conf[$t . "_timming"] * 31 * 24 * 60 * 60;

			//最初に条件を満たす注文IDのみを取得する
			try{
				$res = self::dao()->executeQuery("SELECT id FROM soyshop_order WHERE order_status = " . $mode . " AND order_date < " . $timming . " LIMIT 50");
			}catch(Exception $e){
				continue;
			}

			if(!count($res)) continue;

			//注文IDを集計する
			foreach($res as $v){
				if(!isset($v["id"]) || !is_numeric($v["id"])) continue;
				$orderIds[] = (int)$v["id"];
			}
		}

		if(!count($orderIds)) return;
		$dao = self::dao();

		$dao->begin();

		//soyshop_orders
		foreach(array("orders", "order_attribute", "order_date_attribute", "order_state_history", "slip_number", "returns_slip_number") as $t){
			try{
				$dao->executeUpdateQuery("DELETE FROM soyshop_" . $t . " WHERE order_id IN (" . implode("," , $orderIds) . ")");
			}catch(Exception $e){
				//
			}
		}

		//最後に注文を削除
		try{
			$dao->executeUpdateQuery("DELETE FROM soyshop_order WHERE id IN (" . implode("," , $orderIds) . ")");
		}catch(Exception $e){
			//
		}

		$dao->commit();
	}

	private function dao(){
		static $dao;
		if(isset($dao)) return $dao;
		$dao = new SOY2DAO();
		return $dao;
	}
}
