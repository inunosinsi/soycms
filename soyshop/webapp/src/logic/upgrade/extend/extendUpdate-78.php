<?php
$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
try{
	$results = $itemDao->executeQuery("SELECT id, create_date, update_date FROM soyshop_item WHERE create_date IS NULL");
}catch(Exception $e){
	$results = array();
}
if(count($results)){
	foreach($results as $res){
		if(!isset($res["id"])) continue;
		$updateDate = (isset($res["update_date"]) && is_numeric($res["update_date"])) ? (int)$res["update_date"] : time();
		$createDate = $updateDate;

		$itemId = (is_numeric($res["id"])) ? (int)$res["id"] : 0;
		$item = soyshop_get_item_object($itemId);
		if(!is_numeric($item->getId())) continue;

		$item->setCreateDate($createDate);
		$item->setUpdateDate($updateDate);
		try{
			$itemDao->update($item);
		}catch(Exception $e){
			//
		}

	}
}
unset($itemDao);
