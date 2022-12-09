<?php
$dao = new SOY2DAO();
try{
	$res = $dao->executeQuery("SELECT id FROM soyshop_reserve_calendar_schedule LIMIT 1;");
}catch(Exception $e){
	$res = array();
}

//既にスケジュールの登録があると見なす
if(isset($res[0])){
	//仮で商品の価格を各スケジュールに入れておく
	try{
		$res = $dao->executeQuery("SELECT id, item_price FROM soyshop_item WHERE item_price > 0;");
	}catch(Exception $e){
		$res = array();
	}

	if(isset($res[0])){
		foreach($res as $v){
			try{
				$dao->executeUpdateQuery("UPDATE soyshop_reserve_calendar_schedule SET price = :price WHERE item_id = :itemId", array(":price" => $v["item_price"], ":itemId" => $v["id"]));
			}catch(Exception $e){
				//
			}
		}
	}
}
