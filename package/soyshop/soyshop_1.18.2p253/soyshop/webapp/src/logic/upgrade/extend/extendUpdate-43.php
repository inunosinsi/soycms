<?php
SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->installModule("order_table_index");
$dao = new SOY2DAO();
$try = 0;
for(;;){
	if($try++ > 2) break;	//トライ回数は2回まで
	$res = $dao->executeQuery("SELECT order_id, order_date FROM soyshop_order_state_history GROUP BY order_id, order_date HAVING count(*) > 1 LIMIT 1000");
	if(!count($res)) break;

	foreach($res as $v){
		$results = $dao->executeQuery("SELECT id FROM soyshop_order_state_history WHERE order_id = " . $v["order_id"] . " AND order_date =" . $v["order_date"]);
		if(!count($results)) break;

		foreach($results as $i => $val){
			if($i === 0) continue;
			$dao->executeUpdateQuery("Update soyshop_order_state_history SET order_date = " . ($v["order_date"] + $i) . " WHERE id = " . $val["id"]);
		}
	}
}
