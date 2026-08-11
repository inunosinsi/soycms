<?php
class FixedPointGrantMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$point = SOY2Logic::createInstance("module.plugins.fixed_point_grant.logic.FixedPointGrantLogic")->getTotalPointByOrderId($order->getId());
		if($point > 0){
			$body = array();
			$body[] = "取得ポイント：" . $point . "ポイント";
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
SOYShopPlugin::extension("soyshop.order.mail.user", "fixed_point_grant", "FixedPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "fixed_point_grant", "FixedPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "fixed_point_grant", "FixedPointGrantMailModule");
