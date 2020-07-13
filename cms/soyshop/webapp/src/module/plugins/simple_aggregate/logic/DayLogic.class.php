<?php

class DayLogic extends SOY2LogicBase{

	private $dao;

	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.order.SOYShop_User");
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}

	function calc($orders){
		if(!count($orders)) return array();

		//startの日付を取得
		$start = null;
		foreach($orders as $order){
			if(is_null($start)) $start = $order->getOrderDate();
			if(is_numeric($order->getOrderDate()) && $start > $order->getOrderDate()) $start = $order->getOrderDate();
		}

		$res = array();	//array(yearmonth => array(count => int, male => int, female => int, total => int))
		while(count($orders) > 0){
			list($start, $end) = self::_getStartAndEndTimestamp($start);
			foreach($orders as $idx => $order){
				$key = date("Y", $start) . date("m", $start) . date("d", $start);
				if(!isset($res[$key])) $res[$key] = array("count" => 0, "male" => 0, "female" => 0, "total" => 0);

				//該当する注文
				if($order->getOrderDate() >= $start && $order->getOrderDate() <= $end){
					$res[$key]["count"]++;
					$gender = soyshop_get_user_object($order->getUserId())->getGender();
					if(is_numeric($gender)){
						if($gender == SOYShop_User::USER_SEX_MALE){
							$res[$key]["male"]++;
						}else{
							$res[$key]["female"]++;
						}
					}
					$res[$key]["total"] += SimpleAggregateUtil::priceFilter($order);

					unset($orders[$idx]);
				}
			}
			$start = strtotime("+1 day", $start);
		}

		if(!count($res)) return array();
		$lines = array();

		foreach($res as $key => $v){
			$line = array();
			$line[] = substr($key, 0, 4) . "-" . substr($key, 4, 2) . "-" . substr($key, 6, 2);
			$line[] = $v["count"];
			$line[] = $v["male"];
			$line[] = $v["female"];
			$line[] = $v["total"];
			$line[] = ($v["count"] > 0 && $v["total"] > 0) ? floor($v["total"] / $v["count"]) : 0;
			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	private function _getStartAndEndTimestamp($timestamp){
		$start = soyshop_shape_timestamp($timestamp);
		$end = soyshop_shape_timestamp($timestamp, "end");
		return array($start, $end);
	}

	function getLabels(){
		return array(
			"日付",
			"購入件数",
			"男性",
			"女性",
			"購入合計",
			"購入平均"
		);
	}
}
