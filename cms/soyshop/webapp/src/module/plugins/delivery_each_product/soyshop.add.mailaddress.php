<?php

class DeliveryEachProductAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, bool $orderFlag=false){
        if(!is_numeric($order->getId())) return array();
		$itemOrders = soyshop_get_item_orders((int)$order->getId());
        if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");
        foreach($itemOrders as $itemOrder){
            $mailAddress = DeliveryEachProductUtil::get($itemOrder->getItemId(), DeliveryEachProductUtil::MODE_EMAIL);
            if(!strlen($mailAddress) || !(bool)filter_var($mailAddress, FILTER_VALIDATE_EMAIL)) continue;
            $list[] = $mailAddress;
        }

        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "delivery_each_product", "DeliveryEachProductAddMailAddress");
