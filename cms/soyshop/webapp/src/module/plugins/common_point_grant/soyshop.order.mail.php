<?php
class CommonPointGrantMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$body = array();

		//誕生月ポイントプレゼント分
		SOY2::imports("module.plugins.common_point_grant.util.*");
		$config = PointGrantUtil::getConfig();
		if(isset($config["point_birthday_present"]) && (int)$config["point_birthday_present"] > 0){
			try{
				$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
			}catch(Exception $e){
				$user = new SOYShop_User();
			}

			if(!is_null($user->getId())){
				$birthday = $user->getBirthday();
				if(strlen($birthday)){
					$birthArray = explode("-", $birthday);
					if((int)$birthArray[1] === (int)date("n")){
						$body[] = "誕生月購入特典" . $config["point_birthday_present"] . "ポイントプレゼント";
					}
				}
			}
		}

		$mailLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointMailLogic");
		$body[] = $mailLogic->getOrderCompleteMailContent($order->getUserId());

		return implode("\n", $body);
	}

	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}
SOYShopPlugin::extension("soyshop.order.mail.user", "common_point_grant", "CommonPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_point_grant", "CommonPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_point_grant", "CommonPointGrantMailModule");
