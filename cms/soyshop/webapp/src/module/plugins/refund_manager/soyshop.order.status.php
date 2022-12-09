<?php

class RefundManagerOrderStatus extends SOYShopOrderStatus{

	function paymentStatusItem(){
		return array("21" => array("label" => "返金処理待ち", "mail" => null));
	}
}
SOYShopPlugin::extension("soyshop.order.status", "refund_manager", "RefundManagerOrderStatus");
