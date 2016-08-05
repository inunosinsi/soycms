<?php

class SortingLogic extends SOY2LogicBase{
		
	function execute(){
		//全ユーザのIDを取得
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$sql = "SELECT id FROM soyshop_user WHERE is_disabled != " . SOYShop_User::USER_IS_DISABLED . " AND not_send = " . SOYShop_User::USER_SEND . " AND user_type = ". SOYShop_User::USERTYPE_REGISTER . ";";
		try{
			$res = $userDao->executeQuery($sql, array());
		}catch(Exception $e){
			var_dump($e);
			return;
		}
		
		if(!count($res)) return;
		
		//メモリの節約のため、ここではじめて読み込む
		SOY2::import("module.plugins.attribute_order_count.util.AttributeOrderCountUtil");
		$configs = AttributeOrderCountUtil::getConfig();
		$attr = AttributeOrderCountUtil::getAttrConfig();
		
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$sql = "SELECT COUNT(*) FROM soyshop_order " .
				"WHERE user_id = :userId ".
				"AND order_status >= " . SOYShop_Order::ORDER_STATUS_REGISTERED . " ".
				"AND order_status <= " . SOYShop_Order::ORDER_STATUS_SENDED . " ";
		
		//期間設定がある場合はそれも加味
		$period = (int)AttributeOrderCountUtil::getPeriodConfig();
		
		if(isset($period) && $period > 0) {
			$start = time() - ($period * 24 * 60 * 60);
			$sql .= "AND order_date > " . $start . " ";
		}
		
		foreach($res as $v){
			if(!isset($v["id"])) continue;
			try{
				$res = $orderDao->executeQuery($sql, array(":userId" => $v["id"]));
			}catch(Exception $e){
				continue;
			}
			
			//取得出来なかった場合は次へ
			if(!isset($res[0]["COUNT(*)"])) continue;
			$cnt = (int)$res[0]["COUNT(*)"];
			
			for($i = 0; $i < count($configs); $i++){
								
				$hit = false;
				
				try{
					$user = $userDao->getById($v["id"]);
				}catch(Exception $e){
					continue;
				}
								
				//ゼロ設定
				if((int)$configs[$i]["count"] === 0){
					//注文回数もゼロであるか調べる
					if($cnt === 0) {
						$hit = true;
					}
					
				//ゼロ設定以外
				}else{
					//ゼロ設定がない場合は購入0の会員は無視する
					if($cnt === 0) continue;					
					
					if($configs[$i]["count"] == $cnt){
						$hit = true;
					}else if(isset($configs[$i + 1]) && $configs[$i]["count"] < $cnt && $configs[$i + 1]["count"] > $cnt){
						$i++;
						$hit = true;
					//ラスト
					}else if($configs[$i]["count"] > $cnt){
						$hit = true;
					}
				}
				
				if($hit){
					switch($attr){
						case 1:
							$user->setAttribute1($configs[$i]["label"]);
							break;
						case 2:
							$user->setAttribute2($configs[$i]["label"]);
							break;
						case 3:
							$user->setAttribute3($configs[$i]["label"]);
							break;
					}
					
					try{
						$userDao->update($user);
					}catch(Exception $e){
						//
					}
					break;
				}
			}
		}
	}
}
?>