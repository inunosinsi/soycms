<?php

class AddMailAddressEachItemAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, $orderFlag){
		//取り急ぎ、注文受け付け時のみ
		if(!$orderFlag) return;

        try{
            $itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
        }catch(Exception $e){
            return array();
        }

        if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.add_mailaddress_each_item.util.AddMailAddressEachItemUtil");
        foreach($itemOrders as $itemOrder){
            $mailAddress = trim(AddMailAddressEachItemUtil::get($itemOrder->getItemId(), AddMailAddressEachItemUtil::MODE_EMAIL));
            if(!strlen($mailAddress)) continue;
            /**
             * @ToDo メールアドレスであるかチェックしておきたい
             */
            $list[] = $mailAddress;
        }
		
        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "add_mailaddress_each_item", "AddMailAddressEachItemAddMailAddress");
