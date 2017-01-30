<?php

class SOYShopBonusDownloadOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConfigUtil");
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConditionUtil");
		
		//支払い済みになったら表示する
		if($order->getPaymentStatus() == SOYShop_Order::PAYMENT_STATUS_CONFIRMED || ((isset($_GET["type"])) && $_GET["type"] == "payment")){
			
			$config = BonusDownloadConfigUtil::getConfig();
			$hasBonus = BonusDownloadConditionUtil::hasBonusByOrder($order);
			$urls = BonusDownloadConfigUtil::getOrderAttribute($order, "bonus_download.url_list");
			$urls = explode("\n", $urls);
			
			if($hasBonus && isset($config["name"]) && isset($config["download_url"])){
				$mail = array();
				$mail[] = "";
				$mail[] = $config["name"];
				$mail[] = "-----------------------------------------";
				foreach($urls as $url){
					$mail[] = trim($url);
				}
				$mail[] = "-----------------------------------------\n";
				return implode("\n", $mail);
			}
		}		
	}
	
	function getDisplayOrder(){
		return 10;//コードは100番台
	}

}
SOYShopPlugin::extension("soyshop.order.mail.user", "bonus_download", "SOYShopBonusDownloadOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "bonus_download", "SOYShopBonusDownloadOrderMail");
?>