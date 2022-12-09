<?php

class AgeLogic extends SOY2LogicBase{

	const AGE = 10;

	function __construct(){}

	function calc($orders){
		if(!count($orders)) return array();

		$res = array();
		$none = array();
		foreach($orders as $order){
			$user = soyshop_get_user_object($order->getUserId());
			if(is_null($user->getBirthday()) || !strlen($user->getBirthday())) {	//年齢未回答
				$none[] = SimpleAggregateUtil::priceFilter($order);
			}else{	//年齢あり
				$res[] = array(
					"birthday" => $user->getBirthday(),
					"price" => SimpleAggregateUtil::priceFilter($order)
				);
			}
		}

		if(!count($res) && !count($none)) return array();

		$lines = array();

		// //年代別に振り分ける
		if(count($res)){
			$thisY = (int)date("Y");
			$list = array();
			for($i = 0; $i <= self::AGE; $i++){
				$list[] = array("count" => 0, "total" => 0);
			}

			foreach($res as $v){
				$birthArray = explode("-", $v["birthday"]);
				$age = $thisY - (int)$birthArray[0];
				for($i = 0; $i < 8; $i++){
					$s = $i * 10;
					$e = $s + 9;

					if($age >= $s && $age <= $e){
						$list[$i]["count"]++;
						$list[$i]["total"] += (int)$v["price"];
					}
				}
			}

			//登録
			foreach($list as $n => $v){
				$line = array();
				$num = $n * 10;
				$line[] =  $num . "〜" . ($num + 9) . "歳";
				$line[] = $v["count"];
				$line[] = $v["total"];
				$line[] = ($v["count"] > 0) ? floor($v["total"] / $v["count"]) : 0;

				$lines[] = implode(",", $line);
			}
		}

		//年齢、未回答
		if(count($none)){
			$line = array();
			$line[] = "未回答";
			$cnt = count($none);
			$total = array_sum($none);
			$line[] = $cnt;
			$line[] = $total;
			$line[] = ($cnt > 0) ? floor($total / $cnt) : 0;
			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	function getLabels(){
		return array(
			"年齢",
			"購入件数",
			"購入合計",
			"購入平均"
		);
	}
}
