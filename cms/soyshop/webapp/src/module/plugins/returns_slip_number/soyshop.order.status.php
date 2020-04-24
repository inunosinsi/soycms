<?php

class ReturnsSlipNumberOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		return array(ReturnsSlipNumberUtil::STATUS_CODE => array("label" => "返却済み", "mail" => null));
	}

}
SOYShopPlugin::extension("soyshop.order.status", "returns_slip_number", "ReturnsSlipNumberOrderStatus");
