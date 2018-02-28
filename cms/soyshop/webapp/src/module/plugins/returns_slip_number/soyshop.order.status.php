<?php

class ReturnsSlipNumberOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		return array("21" => "返却済み");
	}

}
SOYShopPlugin::extension("soyshop.order.status", "returns_slip_number", "ReturnsSlipNumberOrderStatus");
