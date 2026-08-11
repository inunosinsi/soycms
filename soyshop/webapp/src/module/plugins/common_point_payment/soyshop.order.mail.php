<?php
class CommonPointPaymentMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		//履歴がなかった場合は何もしない
		$histories = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointMailLogic")->getHistories($order->getUserId(), $order->getId());
		if(count($histories) == 0) return "";

		$mailBody = array();
		foreach($histories as $history){
			$mailBody[] = "ポイント履歴:" . $history->getContent();
		}
		$mailBody[] = "";

		return implode("\n", $mailBody);
	}

	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}
SOYShopPlugin::extension("soyshop.order.mail.user", "common_point_payment", "CommonPointPaymentMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_point_payment", "CommonPointPaymenttMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_point_payment", "CommonPointPaymentMailModule");
