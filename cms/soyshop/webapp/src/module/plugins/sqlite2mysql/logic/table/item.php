<?php

function register_item($stmt){
	$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$items = $dao->get();
			if(!count($items)) break;
		}catch(Exception $e){
			break;
		}

		foreach($items as $item){
			$stmt->execute(array(
				":id" => $item->getId(),
				":item_name" => $item->getName(),
				":item_subtitle" => $item->getSubtitle(),
				":item_code" => $item->getCode(),
				":item_alias" => $item->getAlias(),
				":item_price" => $item->getPrice(),
				":item_purchase_price" => $item->getPurchasePrice(),
				":item_sale_price" => $item->getSalePrice(),
				":item_selling_price" => $item->getSellingPrice(),
				":item_sale_flag" => $item->getSaleFlag(),
				":item_stock" => $item->getStock(),
				":item_unit" => $item->getUnit(),
				":item_config" => $item->getConfig(),
				":item_type" => $item->getType(),
				":item_category" => $item->getCategory(),
				":create_date" => $item->getCreateDate(),
				":update_date" => $item->getUpdateDate(),
				":order_period_start" => $item->getOrderPeriodStart(),
				":order_period_end" => $item->getOrderPeriodEnd(),
				":open_period_start" => $item->getOpenPeriodStart(),
				":open_period_end" => $item->getOpenPeriodEnd(),
				":detail_page_id" => (int)$item->getDetailPageId(),
				":item_is_open" => (int)$item->getIsOpen(),
				":is_disabled" => (int)$item->getIsDisabled()
			));
		}
	}
}
