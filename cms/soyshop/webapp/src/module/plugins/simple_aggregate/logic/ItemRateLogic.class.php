<?php

class ItemRateLogic extends SOY2LogicBase{

	function __construct(){

	}

	function calc($orders){
		if(!count($orders)) return array();

		$list = array();
		foreach($orders as $order){
			$itemOrders = SimpleAggregateUtil::getItemOrdersByOrderId($order->getId());
			if(!count($itemOrders)) continue;
			foreach($itemOrders as $itemOrder){
				if(!isset($list[$itemOrder->getItemId()])) $list[$itemOrder->getItemId()] = array();
				if(!isset($list[$itemOrder->getItemId()]["item_code"])){
					$item = soyshop_get_item_object($itemOrder->getItemId());
					$list[$itemOrder->getItemId()]["item_code"] = $item->getCode();
					$list[$itemOrder->getItemId()]["item_name"] = $item->getName();
					$list[$itemOrder->getItemId()]["item_price"] = $itemz->getPrice();
					$list[$itemOrder->getItemId()]["count"] = 0;
					$list[$itemOrder->getItemId()]["total"] = 0;
				}

				//加算していく
				$list[$itemOrder->getItemId()]["count"] += $itemOrder->getItemCount();
				$list[$itemOrder->getItemId()]["total"] += $itemOrder->getTotalPrice();
			}
		}

		if(!count($list)) return array();

		//ソート用の配列
		$sort_keys = array();
		foreach($list as $itemId => $res){
			if(isset($res["total"])){
				$sort_keys[$itemId] = $res["total"];
			}
		}

		//配列を設定に従い整列
		array_multisort($sort_keys, SORT_DESC, $list);
		if($_POST["Aggregate"]["limit"] > 0){
			if(count($list) > (int)$_POST["Aggregate"]["limit"]){
				array_splice($list, (int)$_POST["Aggregate"]["limit"]);
			}
		}

		if(!count($list)) return array();

		$rank = 1;
		for($i = 0; $i < count($list); $i++){
			$list[$i]["rank"] = $rank++;
		}

		$lines = array();
		foreach($list as $v){
			$line = array();
			$line[] = $v["rank"];
			$line[] = $v["item_code"];
			$line[] = $v["item_name"];
			$line[] = $v["item_price"];
			$line[] = $v["count"];
			$line[] = $v["total"];

			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	function getLabels(){
		return array(
			"順位",
			"商品番号",
			"商品名",
			"商品単価",
			"購入件数",
			"金額"
		);
	}
}
