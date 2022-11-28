<?php

class BlackCustomerListOrderCustomfield extends SOYShopOrderCustomfield{

	function display(int $orderId){
		SOY2::import("module.plugins.black_customer_list.util.BlackCustomerListUtil");
		if((int)soyshop_get_user_attribute_value(soyshop_get_order_object($orderId)->getUserId(), BlackCustomerListUtil::PLUGIN_ID, "int")){
			return array(array(
				"name" => "ブラックリスト",
				"value" => "ブラックリストに登録されています。",
				"style" => "font-weight:bold;color:#FF0000;"
			));
		}
		return array();
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "black_customer_list", "BlackCustomerListOrderCustomfield");
