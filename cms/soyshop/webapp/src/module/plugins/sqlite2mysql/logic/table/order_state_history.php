<?php

function register_order_state_history($stmt){
	$dao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$histories = $dao->get();
			if(!count($histories)) break;
		}catch(Exception $e){
			break;
		}

		foreach($histories as $history){
			$stmt->execute(array(
				":id" => $history->getId(),
				":order_id" => $history->getOrderId(),
				":order_date" => $history->getDate(),
				":author" => $history->getAuthor(),
				":content" => $history->getContent(),
				":more" => $history->getMore()
			));
		}
	}
}
