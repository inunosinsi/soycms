<?php
class CommonTicketBaseMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$count = SOY2Logic::createInstance("module.plugins.common_ticket_base.logic.TicketBaseLogic")->getTotalCountByOrderId($order->getId());
		if($count > 0){
			SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
			$config = TicketBaseUtil::getConfig();

			$body = array();
			$body[] = "取得" . $config["label"] . "：" . $count . $config["unit"];
			return implode("\n", $body);
		}

		// @ToDo 現在のポイントを通知する必要があれば復元する
		// $mailLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointMailLogic");
		// $body[] = $mailLogic->getOrderCompleteMailContent($order->getUserId());
	}

	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}
SOYShopPlugin::extension("soyshop.order.mail.user", "common_ticket_base", "CommonTicketBaseMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_ticket_base", "CommonTicketBaseMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_ticket_base", "CommonTicketBaseMailModule");
