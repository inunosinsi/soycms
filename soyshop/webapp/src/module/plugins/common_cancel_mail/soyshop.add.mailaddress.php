<?php

class CommonCancelMailAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, bool $orderFlag=false){

		//ステータスがキャンセルでない時は動作しない
		if($order->getStatus() != SOYShop_Order::ORDER_STATUS_CANCELED) return array();

		$itemOrders = soyshop_get_item_orders($order->getId());
        if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.common_cancel_mail.util.CancelMailUtil");
        foreach($itemOrders as $itemOrder){
			$mailAddress = soyshop_get_item_attribute_value($itemOrder->getItemId(), CancelMailUtil::PLUGIN_ID . "_" . CancelMailUtil::MODE_EMAIL, "string");
            if(!strlen($mailAddress) || !(bool)filter_var($mailAddress, FILTER_VALIDATE_EMAIL)) continue;
			$list[] = $mailAddress;
        }

        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "common_cancel_mail", "CommonCancelMailAddMailAddress");
