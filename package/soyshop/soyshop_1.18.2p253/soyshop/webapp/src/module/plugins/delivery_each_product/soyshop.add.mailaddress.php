<?php

class DeliveryEachProductAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, $orderFlag){

        try{
            $itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
        }catch(Exception $e){
            return array();
        }

        if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");
        foreach($itemOrders as $itemOrder){
            $mailAddress = DeliveryEachProductUtil::get($itemOrder->getItemId(), DeliveryEachProductUtil::MODE_EMAIL);
            if(!strlen($mailAddress)) continue;
            /**
             * @ToDo メールアドレスであるかチェックしておきたい
             */
            $list[] = $mailAddress;
        }

        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "delivery_each_product", "DeliveryEachProductAddMailAddress");
