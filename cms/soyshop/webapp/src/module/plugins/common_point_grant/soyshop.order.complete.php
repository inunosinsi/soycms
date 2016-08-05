<?php
class CommonPointGrantOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$cart = CartLogic::getCart();
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		$totalPoint = $logic->getTotalPointAfterPaymentPoint($cart, $order);
		
		if($totalPoint > 0){
			$logic->insertPoint($order, (int)$totalPoint);
		}
		
		//誕生月特典
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
					$logic->insert($config["point_birthday_present"], "誕生月購入特典" . $config["point_birthday_present"] . "ポイントプレゼント", $user->getId());
				}
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_point_grant", "CommonPointGrantOrderComplete");
?>