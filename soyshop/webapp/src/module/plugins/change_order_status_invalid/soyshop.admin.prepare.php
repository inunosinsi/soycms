<?php

class ChangeOrderStatusInvalidAdminPrepare extends SOYShopAdminPrepareAction{

	function prepare(){
		SOY2::import("module.plugins.change_order_status_invalid.util.ChangeOrderStatusInvalidUtil");
		ChangeOrderStatusInvalidUtil::changeInvalidStatusOlderOrder();
	}
}
SOYShopPlugin::extension("soyshop.admin.prepare", "change_order_status_invalid", "ChangeOrderStatusInvalidAdminPrepare");
