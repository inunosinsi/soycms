<?php
$dao = new SOY2DAO();
for(;;){
	try{
		$res = $dao->executeQuery("SELECT order_id, order_date FROM soyshop_order_state_history GROUP BY order_id, order_date HAVING count(*) > 1 LIMIT 100");
	}catch(Exception $e){
		$res = array();
	}
	if(!count($res)) break;

	foreach($res as $v){
		try{
			$results = $dao->executeQuery("SELECT id FROM soyshop_order_state_history WHERE order_id = " . $v["order_id"] . " AND order_date =" . $v["order_date"]);
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) break;

		foreach($results as $i => $val){
			if($i === 0) continue;
			try{
				$dao->executeUpdateQuery("Update soyshop_order_state_history SET order_date = " . ($v["order_date"] + $i) . " WHERE id = " . $val["id"]);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}
}
