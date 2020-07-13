<?php

class OrderDateCustomFieldLogic extends SOY2LogicBase{

	private $dao;
	private $fieldId;
	private $dateFieldId;	//オーダーカスタムフィールド(日付)
	private $binds = array();

	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.order.SOYShop_User");
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}

	function calc($orders){
		//パラメータのセット
		if(strlen($this->dateFieldId)) $this->binds[":dateFieldId"] = $this->dateFieldId;
		if(isset($_POST["AggregateHiddenValue"]["field_value"]) && strlen($_POST["AggregateHiddenValue"]["field_value"])){
			$this->binds[":fieldId"] = $this->fieldId;
			$this->binds[":fieldValue"] = $_POST["AggregateHiddenValue"]["field_value"];
		}

		$start = AggregateUtil::convertTitmeStamp("start", true, true);
		$end = AggregateUtil::convertTitmeStamp("end");

		//結果を格納する配列
		$results = array();


		while($start < $end){

			//次の日のタイムスタンプ
			$p = $start + 24*60*60;

			$res = self::executeSql($start, $p);

			$values = array();
			$values[] = date("Y-m-d", $start);

			if(count($res)){
				//結果

				$userIds = array();
				$count = count($res);
				$total = 0;
				$logic = SOY2Logic::createInstance("module.plugins.common_aggregate.logic.AggregateLogic");
				foreach($res as $v){
					if(isset($v["user_id"])){
						//keyのindexにuser_idでvalueに出現回数
						if(array_key_exists($v["user_id"], $userIds)){
							$userIds[$v["user_id"]]++;
						}else{
							$userIds[$v["user_id"]] = 1;
						}
					}
					$total += $logic->calc($v);
				}

				//取得した顧客ID毎に性別別の注文回数を調べる
				list($maleCnt, $femaleCnt) = self::getGenderCount($userIds);

				$values[] = $count;
				$values[] = $maleCnt;
				$values[] = $femaleCnt;
				$values[] = $total;
				$values[] = floor($total / $count);

			}else{
				$values[] = 0;
				$values[] = 0;
				$values[] = 0;
				$values[] = 0;
				$values[] = 0;
			}

			$results[] = implode(",", $values);

			//開始時刻を次の日にする
			$start = $p;
		}

		return $results;
	}

	//SQLを実行する
	private function executeSql($start, $end){
		$this->binds[":start"] = $start;
		$this->binds[":end"] = $end;
		try{
			$res = $this->dao->executeQuery(self::buildSql(), $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		return $res;
	}

	private function buildSql(){
		$sql = "SELECT price, user_id, modules FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ";

		if(strlen($this->dateFieldId)){
			$sql .= "AND id IN (".
				"SELECT order_id FROM soyshop_order_date_attribute ".
				"WHERE order_field_id = :dateFieldId ".
				"AND order_value_1 >= :start ".
				"AND order_value_1 < :end".
			") ";
		}

		//隠しモード
		if(isset($_POST["AggregateHiddenValue"]["field_value"]) && strlen($_POST["AggregateHiddenValue"]["field_value"])){
			$sql .= "AND id IN (".
				"SELECT order_id FROM soyshop_order_attribute ".
				"WHERE order_field_id = :fieldId ".
				"AND order_value1 = :fieldValue".
			") ";
		}

		return $sql;
	}

	private function getGenderCount($userIds){

		$maleCnt = 0;
		$femaleCnt = 0;

		foreach($userIds as $userId => $cnt){
			try{
				$res = $this->dao->executeQuery("SELECT gender FROM soyshop_user WHERE id = :id AND is_disabled != 1", array(":id" => $userId));
			}catch(Exception $e){
				$res = array();
			}

			if(!isset($res[0]["gender"])) continue;

			if($res[0]["gender"] == SOYShop_User::USER_SEX_MALE){
				$maleCnt += $cnt;
			}else if($res[0]["gender"] == SOYShop_User::USER_SEX_FEMALE){
				$femaleCnt += $cnt;
			}else{
				//
			}
		}

		return array($maleCnt, $femaleCnt);
	}

	function getLabels(){
		$label = array();
		$label[] = (isset($_POST["AggregateHiddenValue"]["first_column"])) ? $_POST["AggregateHiddenValue"]["first_column"] : "日付";
		$label[] = "購入件数";
		$label[] = "男性";
		$label[] = "女性";
		$label[] = "購入合計";
		$label[] = "購入平均";

		return $label;
	}

	function setDateFieldId($dateFieldId){
		$this->dateFieldId = $dateFieldId;
	}

	function setFieldId($fieldId){
		$this->fieldId = $fieldId;
	}
}
