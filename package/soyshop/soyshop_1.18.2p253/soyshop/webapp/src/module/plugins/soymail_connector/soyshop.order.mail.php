<?php

class SOYMailConnectorOrderMail extends SOYShopOrderMail{
	
	const PLUGIN_ID = "soymail_connector";
	
	function prepare(){
		if(!class_exists("SOYMailConnectoryUtil")){
			SOY2::import("module.plugins." . self::PLUGIN_ID . ".util.SOYMailConnectorUtil");
		}
	}

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$this->prepare();
		
		$mail = "";
		
		$config = SOYMailConnectorUtil::getConfig();
		$isInsertMail = (isset($config["isInsertMail"]) && (int)$config["isInsertMail"] === SOYMailConnectorUtil::INSERT);
		if($isInsertMail){
			$user = $this->getUser($order->getUserId());
			if(!is_null($user->getId())){
				$send = ($user->getNotSend() == SOYShop_User::USER_SEND) ? "配信を希望する" : "配信を希望しない";
				$mail = "メールマガジン:" . $send;
			}
		}
		
		return $mail;
	}
	
	function getUser($userId){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDao->getById($userId);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
		return $user;
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "soymail_connector", "SOYMailConnectorOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "soymail_connector", "SOYMailConnectorOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "soymail_connector", "SOYMailConnectorOrderMail");
?>