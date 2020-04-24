<?php

class CommonCancelMailAddMailAddress extends SOYShopAddMailAddress{

    function getMailAddress(SOYShop_Order $order, $orderFlag){

		//ステータスがキャンセルでない時は動作しない
		if($order->getStatus() != SOYShop_Order::ORDER_STATUS_CANCELED) return array();

        try{
            $itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
        }catch(Exception $e){
            return array();
        }

        if(!count($itemOrders)) return array();

        $list = array();

        SOY2::import("module.plugins.common_cancel_mail.util.CancelMailUtil");
        foreach($itemOrders as $itemOrder){
            $mailAddress = trim(CancelMailUtil::get($itemOrder->getItemId(), CancelMailUtil::MODE_EMAIL));
            if(!strlen($mailAddress) || !(bool)filter_var($mailAddress, FILTER_VALIDATE_EMAIL)) continue;
			$list[] = $mailAddress;
        }

        return $list;
    }
}
SOYShopPlugin::extension("soyshop.add.mailaddress", "common_cancel_mail", "CommonCancelMailAddMailAddress");
