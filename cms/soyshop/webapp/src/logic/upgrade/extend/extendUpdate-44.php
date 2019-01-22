<?php
$dao = new SOY2DAO();
for(;;){
	try{
		$res = $dao->executeQuery("SELECT order_id, item_id, cdate FROM soyshop_orders GROUP BY order_id, item_id, cdate HAVING count(*) > 1 LIMIT 100");
	}catch(Exception $e){
		$res = array();
	}

	if(!count($res)) break;

	foreach($res as $v){
		try{
			$results = $dao->executeQuery("SELECT id FROM soyshop_orders WHERE order_id = " . $v["order_id"] . " AND item_id = " . $v["item_id"] . " AND cdate =" . $v["cdate"]);
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) break;

		foreach($results as $i => $val){
			if($i === 0) continue;
			try{
				$dao->executeUpdateQuery("Update soyshop_orders SET cdate = " . ($v["cdate"] + $i) . " WHERE id = " . $val["id"]);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}
}
