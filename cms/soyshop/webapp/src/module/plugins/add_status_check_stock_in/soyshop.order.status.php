<?php

class AddStatusCheckStockInOrderStatus extends SOYShopOrderStatus{

	function statusItem(){
		return array("11" => array("label" => "在庫確認中", "mail" => null));
	}

}
SOYShopPlugin::extension("soyshop.order.status", "add_status_check_stock_in", "AddStatusCheckStockInOrderStatus");
