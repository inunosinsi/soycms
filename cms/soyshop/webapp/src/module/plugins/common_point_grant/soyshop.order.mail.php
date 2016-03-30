<?php
class CommonPointGrantMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$mailLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointMailLogic");
		return $mailLogic->getOrderCompleteMailContent($order->getUserId());
	}
	
	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}
SOYShopPlugin::extension("soyshop.order.mail.user", "common_point_grant", "CommonPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_point_grant", "CommonPointGrantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_point_grant", "CommonPointGrantMailModule");
?>