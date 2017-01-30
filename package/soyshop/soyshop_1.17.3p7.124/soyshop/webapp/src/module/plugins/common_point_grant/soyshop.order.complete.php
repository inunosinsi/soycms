<?php
class CommonPointGrantOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$cart = CartLogic::getCart();
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		$totalPoint = $logic->getTotalPointAfterPaymentPoint($cart, $order);
		
		if($totalPoint > 0){
			$logic->insertPoint($order, (int)$totalPoint);
		}
		
		/**誕生月特典**/
		SOY2::imports("module.plugins.common_point_grant.util.*");
		$config = PointGrantUtil::getConfig();
		if(isset($config["point_birthday_present"]) && (int)$config["point_birthday_present"] > 0){
			try{
				$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
			}catch(Exception $e){
				return;
			}
			
			$birthday = $user->getBirthday();
			if(strlen($birthday)){
				$birthArray = explode("-", $birthday);
				if((int)$birthArray[1] === (int)date("n")){
					//念の為、今月すでに購入していないか調べる
					$dao = new SOY2DAO();
					$sql = "SELECT content FROM soyshop_point_history WHERE user_id = :userId AND create_date > :start AND create_date < :end ";
					
					try{
						$res = $dao->executeQuery($sql, array(":userId" => $user->getId(), ":start" => self::getDateTimestamp("start"), ":end" => self::getDateTimestamp("end")));
					}catch(Exception $e){
						$res = array();
					}
					
					if(count($res)){
						foreach($res as $v){
							//すでにポイントプレゼントが発生しているので処理をやめる
							if(strpos($v["content"], "誕生月購入特典") === 0) return;
						}
					}
					
					$logic->insert($config["point_birthday_present"], "誕生月購入特典" . $config["point_birthday_present"] . "ポイントプレゼント", $user->getId());
				}
			}
		}
	}
	
	private function getDateTimestamp($mode = "start"){
		$arr = explode("-", date("Y-n-j"));
		
		if($mode == "start"){
			return mktime(0, 0, 0, $arr[1], 1, $arr[0]);
		}else{
			return mktime(0, 0, 0, $arr[1] + 1, 1, $arr[0]) - 1;
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_point_grant", "CommonPointGrantOrderComplete");
?>