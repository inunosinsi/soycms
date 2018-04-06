<?php

class ReturnsSlipNumberOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		return array("21" => array("label" => "返却済み", "mail" => null));
	}

}
SOYShopPlugin::extension("soyshop.order.status", "returns_slip_number", "ReturnsSlipNumberOrderStatus");
