<?php

class DayLogic extends SOY2LogicBase{
	
	private $dao;
	
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.order.SOYShop_User");
		SOY2::import("domain.order.SOYShop_ItemModule");
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}
	
	function calc(){
		$start = AggregateUtil::convertTitmeStamp("start");
		$end = AggregateUtil::convertTitmeStamp("end");
		
		//今日よりも後の日を指定したら今日の時刻に変更する
		if($end > time()) $end = time();
		
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
				foreach($res as $v){
					if(isset($v["user_id"])){
						//keyのindexにuser_idでvalueに出現回数
						if(array_key_exists($v["user_id"], $userIds)){
							$userIds[$v["user_id"]]++;
						}else{
							$userIds[$v["user_id"]] = 1;
						}
					}
					$orderPrice = (int)$v["price"];
					$modules = $this->dao->getObject($v)->getModuleList();

					//消費税を除く
					if(AGGREGATE_WITHOUT_TAX){
						foreach($modules as $key => $module){
							if(strpos($module->getType(), "tax") !== false){
								//外税か内税かを調べる。falseの場合は外税
								if(!$module->getIsInclude() && $module->getPrice() > 0){
									$orderPrice -= (int)$module->getPrice();
								}
							}
						}
					}
										
					//手数料を除く
					if(AGGREGATE_WITHOUT_COMMISSION){
						foreach($modules as $key => $module){
							if(strpos($module->getType(), "delivery_") !== false || strpos($module->getType(), "payment_") !== false){
								if(!$module->getIsInclude() && $module->getPrice() > 0){
									$orderPrice -= (int)$module->getPrice();
								}
							}
						}
					}
					
					$total += $orderPrice;
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
		try{
			$res = $this->dao->executeQuery(self::buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			$res = array();
		}
		
		return $res;
	}
	
	private function buildSql(){
		return "SELECT price, user_id, modules FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date > :start ".
				"AND order_date < :end";
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
		$label[] = "日付";
		$label[] = "購入件数";
		$label[] = "男性";
		$label[] = "女性";
		$label[] = "購入合計";
		$label[] = "購入平均";
		
		return $label;
	}
}
?>