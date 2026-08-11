<?php

function register_orders($stmt){
	$dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$itemOrders = $dao->get();
			if(!count($itemOrders)) break;
		}catch(Exception $e){
			break;
		}

		foreach($itemOrders as $itemOrder){
			$stmt->execute(array(
				":id" => $itemOrder->getId(),
				":order_id" => $itemOrder->getOrderId(),
				":item_id" => $itemOrder->getItemId(),
				":item_count" => $itemOrder->getItemCount(),
				":item_price" => $itemOrder->getItemPrice(),
				":total_price" => $itemOrder->getTotalPrice(),
				":item_name" => $itemOrder->getItemName(),
				":status" => $itemOrder->getStatus(),
				":flag" => $itemorder->getFlag(),
				":cdate" => $itemOrder->getCdate(),
				":is_sended" => $itemOrder->getIsSended(),
				":attributes" => $itemOrder->getAttributes(),
				":is_addition" => $itemOrder->getIsAddition(),
				":is_confirm" => $itemOrder->getIsConfirm(),
				":display_order" => $itemOrder->getDisplayOrder()
			));
		}
	}
}
