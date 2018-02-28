<?php

class AddStatusReturnWaitOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		return array("12" => "返品待ち");
	}

}
SOYShopPlugin::extension("soyshop.order.status", "add_status_return_wait", "AddStatusReturnWaitOrderStatus");
