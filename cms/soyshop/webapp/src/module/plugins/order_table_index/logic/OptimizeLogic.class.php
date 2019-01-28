<?php

class OptimizeLogic extends SOY2LogicBase{

	function optimize($limit=1){
		$dao = new SOY2DAO();

		//update-43の処理
		$res = $dao->executeQuery("SELECT order_id, order_date FROM soyshop_order_state_history GROUP BY order_id, order_date HAVING count(*) > 1 LIMIT " . $limit);
		if(count($res)){
			foreach($res as $v){
				$results = $dao->executeQuery("SELECT id FROM soyshop_order_state_history WHERE order_id = " . $v["order_id"] . " AND order_date =" . $v["order_date"]);
				if(!count($results)) {
					self::createIndex("order_state_history");
					break;
				}

				foreach($results as $i => $val){
					if($i === 0) continue;
					$dao->executeUpdateQuery("Update soyshop_order_state_history SET order_date = " . ($v["order_date"] + $i) . " WHERE id = " . $val["id"]);
				}
			}
		}

		$res2 = array();
		if(!count($res)){
			$res2 = $dao->executeQuery("SELECT order_id, item_id, cdate FROM soyshop_orders GROUP BY order_id, item_id, cdate HAVING count(*) > 1 LIMIT " . $limit);
			if(count($res2)){
				foreach($res2 as $v){
					$results = $dao->executeQuery("SELECT id FROM soyshop_orders WHERE order_id = " . $v["order_id"] . " AND item_id = " . $v["item_id"] . " AND cdate =" . $v["cdate"]);
					if(!count($results)) {
						self::createIndex("orders");
						break;
					}

					foreach($results as $i => $val){
						if($i === 0) continue;
						$dao->executeUpdateQuery("Update soyshop_orders SET cdate = " . ($v["cdate"] + $i) . " WHERE id = " . $val["id"]);
					}
				}
			}
		}


		//メールログの最適化
		$res3 = array();
		if(!count($res) && !count($res2)){
			$res3 = $dao->executeQuery("SELECT order_id, user_id, send_date FROM soyshop_mail_log GROUP BY order_id, user_id, send_date HAVING count(*) > 1 LIMIT " . $limit);
			if(count($res3)){
				foreach($res3 as $v){
					$results = $dao->executeQuery("SELECT id FROM soyshop_mail_log WHERE order_id = " . $v["order_id"] . " AND user_id = " . $v["user_id"] . " AND send_date =" . $v["send_date"]);
					if(!count($results)) {
						self::createIndex("mail_log");
						break;
					}

					foreach($results as $i => $val){
						if($i === 0) continue;
						$dao->executeUpdateQuery("DELETE FROM soyshop_mail_log WHERE id = " . $val["id"]);
					}
				}
			}
		}

		//すべてが終わったら、インデックスを張ってプラグインをアンインストールする
		if(!count($res) && !count($res2) && !count($res3)){
			self::createIndex();

			SOY2::import("util.SOYShopPluginUtil");
			if(SOYShopPluginUtil::checkIsActive("order_table_index")){
				SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->uninstallModule("order_table_index");
			}
		}
	}

	private function createIndex($alias=null){
		$sqls = file_get_contents(SOY2::RootDir() . "logic/upgrade/sql/" . SOYSHOP_DB_TYPE ."/update-45.sql");
		if(strlen($sqls)){
			$dao = new SOY2DAO();
			//コメント削除
			$sqls = preg_replace("/#.*\$/m", "", $sqls);

			//改行統一
			$sqls = strtr($sqls, array("\r\n" => "\n", "\r" => "\n"));

			$sqls = explode(";", $sqls);
			foreach($sqls as $sql){
				if(strlen(trim($sql)) < 1) continue;
				if(isset($alias) && strpos($sql, "soyshop_" . $alias) === false) continue;
				
				try{
					$dao->executeUpdateQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}
	}
}
