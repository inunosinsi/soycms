<?php

class CustomerLogic extends SOY2LogicBase{

	private $dao;

	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}

	function calc(){
		list($start, $end) = AggregateUtil::getDatePeriodBySelectBox();

		//指定の期間の注文をすべて取得する
		try{
			$res = $this->dao->executeQuery(self::buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		//user_id => array(price, order_id => array())
		$list = array();
		$logic = SOY2Logic::createInstance("module.plugins.common_aggregate.logic.AggregateLogic");
		foreach($res as $vals){
			if(array_key_exists($vals["user_id"], $list)){

				//最高額フィルターがある場合では合計金額の加算は行わない
				if(isset($_POST["Aggregate"]["filter"]["order"]["max"])) continue;

				$list[$vals["user_id"]]["price"] += $logic->calc($vals);
				$list[$vals["user_id"]]["order_ids"][] = (int)$vals["id"];
			}else{
				$v = array();
				$v["price"] = $logic->calc($vals);
				$v["order_ids"][] = (int)$vals["id"];
				$v["user_id"] = (int)$vals["user_id"];
				$list[$vals["user_id"]] = $v;
			}
		}

		//商品名フィルターがある場合はここで合計金額を上書きする
		if(isset($_POST["Aggregate"]["filter"]["item"]) && strlen($_POST["Aggregate"]["filter"]["item"])){
			$array = array();
			foreach($list as $k => $v){
				$v["price"] = self::calcByItemFilter($v["order_ids"]);
				$array[$k] = $v;
			}
			$list = $array;
			unset($array);
		}

		//価格帯フィルター　遅くなるけど、PHPで集計した後に調べる
		$f_min = (isset($_POST["Aggregate"]["filter"]["price"]["min"]) && (int)$_POST["Aggregate"]["filter"]["price"]["min"] > 0) ? (int)$_POST["Aggregate"]["filter"]["price"]["min"] : null;
		$f_max = (isset($_POST["Aggregate"]["filter"]["price"]["max"]) && (int)$_POST["Aggregate"]["filter"]["price"]["max"] > 0) ? (int)$_POST["Aggregate"]["filter"]["price"]["max"] : null;
		if($f_min > 0 || $f_max > 0){
			$array = array();
			foreach($list as $k => $v){

				//最小フィルターの設定がある時
				if($f_min > 0 && (int)$v["price"] < $f_min) continue;

				//最大フィルターの設定がある時
				if($f_max > 0 && (int)$v["price"] > $f_max) continue;

				$array[$k] = $v;
			}
			$list = $array;
			unset($array);
		}

		//ソート用の配列
		$sort_keys = array();
		foreach($list as $key => $l){
			if(isset($l["price"])){
				$sort_keys[$key] = (int)$l["price"];
			}
		}

		//配列を設定に従い整列
		array_multisort($sort_keys, SORT_DESC, $list);

		$lines = array();

		foreach($list as $vals){
			$line = array();
			$user = soyshop_get_user_object($vals["user_id"]);
			$line[] = $user->getName();	//名前
			$line[] = $user->getMailAddress();		//メールアドレス

			$tel = trim($user->getTelephoneNumber());
			$line[] = (strlen($tel)) ? "=\"" . $tel . "\"" : "";	//電話番号

			$line[] = $vals["price"];						//合計金額

			$itemList = self::getPurcharedItemNameList($vals["order_ids"]);
			$items = array();
			foreach($itemList as $i){
				$items[] = $i["item_name"];
			}
			$line[] = implode(",", $items);

			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	private function buildSql(){
		$sql = "SELECT id, price, user_id, modules FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end ";

		//顧客名フィルタがある場合
		if(isset($_POST["Aggregate"]["filter"]["customer"]) && strlen($_POST["Aggregate"]["filter"]["customer"])){
			$sql .= "AND user_id IN (" .
						"SELECT id FROM soyshop_user " .
						"WHERE name LIKE '%" . htmlspecialchars($_POST["Aggregate"]["filter"]["customer"], ENT_QUOTES, "UTF-8") . "%' ".
						"OR reading LIKE '%" . htmlspecialchars($_POST["Aggregate"]["filter"]["customer"], ENT_QUOTES, "UTF-8") . "%'".
					") ";
		}

		//商品フィルターがある場合
		if(isset($_POST["Aggregate"]["filter"]["item"]) && strlen($_POST["Aggregate"]["filter"]["item"])){
			$sql .= "AND id IN (" .
						"SELECT order_id FROM soyshop_orders " .
						"WHERE item_name LIKE '%" . htmlspecialchars($_POST["Aggregate"]["filter"]["item"], ENT_QUOTES, "UTF-8") . "%' ".
					") ";
		}

		//支払額最高値フィルターがある場合
		if(isset($_POST["Aggregate"]["filter"]["order"]["max"])){
			$sql .= "AND price IN (" .
						"SELECT MAX(price) FROM soyshop_order GROUP BY user_id" .
					") ";
		}

		return $sql;
	}

	private function calcByItemFilter($orderIds){
		$sql = "SELECT SUM(total_price) AS TOTAL FROM soyshop_orders ".
				"WHERE order_id IN (" . implode(",", $orderIds) . ") ".
				"AND item_name LIKE '%" . htmlspecialchars($_POST["Aggregate"]["filter"]["item"], ENT_QUOTES, "UTF-8") . "%' ";

		try{
			$res = $this->dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
	}

	private function getPurcharedItemNameList($orderIds){
		$sql = "SELECT DISTINCT item_name FROM soyshop_orders ".
				"WHERE order_id IN (" . implode(",", $orderIds) . ") ";

		//商品名フィルターがある場合は関係ない商品を除く
		if(isset($_POST["Aggregate"]["filter"]["item"]) && strlen($_POST["Aggregate"]["filter"]["item"])){
			$sql .= "AND item_name LIKE '%" . htmlspecialchars($_POST["Aggregate"]["filter"]["item"], ENT_QUOTES, "UTF-8") . "%' ";
		}

		try{
			return $this->dao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
	}

	function getLabels(){
		$label = array();
		$label[] = "顧客名";
		$label[] = "顧客メールアドレス";
		$label[] = "顧客電話番号";
		$label[] = "購入合計";
		$label[] = "購入商品";

		return $label;
	}
}
