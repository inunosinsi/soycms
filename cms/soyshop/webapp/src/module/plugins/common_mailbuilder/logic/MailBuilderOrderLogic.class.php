<?php

class MailBuilderOrderLogic extends SOY2LogicBase{

    function getItemsByOrderId($orderId) {
    	SOY2::import("module.plugins.common_mailbuilder.common.CommonMailbuilderCommon");
    	$sortConfig = CommonMailbuilderCommon::getSortConfig();

		$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		$sql = "SELECT os.* FROM soyshop_orders os " .
				"INNER JOIN soyshop_item i ".
				"ON os.item_id = i.id ".
				" WHERE os.order_id = :oid ";

		//sort
		$s = ($sortConfig["isReverse"] == 1) ? "DESC" : "ASC";

		switch($sortConfig["defaultSort"]){
			case "name":
				$sql .= "ORDER BY i.item_name " . $s;
				break;
			case "code":
				$sql .= "ORDER BY i.item_code " . $s;
				break;
			case "cdate":
				$sql .= "ORDER BY i.create_date " . $s;
				break;
			case "udate":
				$sql .= "ORDER BY i.update_date " . $s;
				break;
		}

		try{
			$res = $itemOrderDao->executeQuery($sql, array(":oid" => $orderId));
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$array = array();

		foreach($res as $v){
			$array[] = $itemOrderDao->getObject($v);
		}

		return $array;
	}
}
