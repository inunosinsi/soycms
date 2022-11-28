<?php

function register_order($stmt){
	$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$orders = $dao->get();
			if(!count($orders)) break;
		}catch(Exception $e){
			break;
		}

		foreach($orders as $order){
			$stmt->execute(array(
				":id" => $order->getId(),
				":order_date" => (int)$order->getOrderDate(),
				":price" => (int)$order->getPrice(),
				":order_status" => (int)$order->getStatus(),
				":payment_status" => (int)$order->getPaymentStatus(),
				":address" => $order->getAddress(),
				":claimed_address" => $order->getClaimedAddress(),
				":user_id" => (int)$order->getUserId(),
				":attributes" => $order->getAttributes(),
				":modules" => $order->getModules(),
				":mail_status" => $order->getMailStatus(),
				":tracking_number" => $order->getTrackingNumber()
			));
		}
	}
}
