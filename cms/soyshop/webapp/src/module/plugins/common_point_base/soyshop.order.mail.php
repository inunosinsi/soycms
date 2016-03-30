<?php
class CommonPointBaseMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		//common_point_grantに移行
	}
	
	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}
SOYShopPlugin::extension("soyshop.order.mail.user", "common_point_base", "CommonPointBaseMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_point_base", "CommonPointBaseMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_point_base", "CommonPointBaseMailModule");
?>