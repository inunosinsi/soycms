<?php
$dao = new SOY2DAO();
$try = 0;
for(;;){
	if($try++ > 2) break;	//トライ回数は2回まで
	$res = $dao->executeQuery("SELECT order_id, item_id, cdate FROM soyshop_orders GROUP BY order_id, item_id, cdate HAVING count(*) > 1 LIMIT 1000");
	if(!count($res)) {
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("order_table_index")){
			SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->uninstallModule("order_table_index");
		}
		break;
	}

	foreach($res as $v){
		$results = $dao->executeQuery("SELECT id FROM soyshop_orders WHERE order_id = " . $v["order_id"] . " AND item_id = " . $v["item_id"] . " AND cdate =" . $v["cdate"]);
		if(!count($results)) break;

		foreach($results as $i => $val){
			if($i === 0) continue;
			$dao->executeUpdateQuery("Update soyshop_orders SET cdate = " . ($v["cdate"] + $i) . " WHERE id = " . $val["id"]);
		}
	}
}
