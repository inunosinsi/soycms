<?php

class AddStatusReturnWaitOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		return array("12" => array("label" => "返品待ち", "mail" => null));
	}

}
SOYShopPlugin::extension("soyshop.order.status", "add_status_return_wait", "AddStatusReturnWaitOrderStatus");
