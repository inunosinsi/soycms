<?php

class AddMailAddressEachItemAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, bool $orderFlag=false){
		//取り急ぎ、注文受け付け時のみ
		if(!$orderFlag) return array();

		$itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($order->getId());
		if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.add_mailaddress_each_item.util.AddMailAddressEachItemUtil");
        foreach($itemOrders as $itemOrder){
            $mailAddress = trim(soyshop_get_item_attribute_value($itemOrder->getItemId(), AddMailAddressEachItemUtil::PLUGIN_ID . "_" . AddMailAddressEachItemUtil::MODE_EMAIL, "string"));
            if(!strlen($mailAddress) || !(bool)filter_var($mailAddress, FILTER_VALIDATE_EMAIL)) continue;
            $list[] = $mailAddress;
        }

        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "add_mailaddress_each_item", "AddMailAddressEachItemAddMailAddress");
