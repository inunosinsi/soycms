<?php

class DeliveryEachProductModule extends SOYShopDelivery{

    function onSelect(CartLogic $cart){
        $module = new SOYShop_ItemModule();
        $module->setId("delivery_each_product");
        $module->setName("送料");
        $module->setType("delivery_module");    //typeを指定しておくといいことがある
        $module->setPrice($this->getPrice());
        $cart->addModule($module);
    }

    function getName(){
        return "宅配便";
    }

    function getDescription(){
        /** @ToDo どの商品がどれくらいの送料か？は載せたい **/

    }

    function getPrice(){
        $price = 0;

        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");

        $user = $this->getCart()->getCustomerInformation();

        $itemOrders = $this->getCart()->getItems();
        foreach($itemOrders as $itemOrder){
            $v = DeliveryEachProductUtil::get($itemOrder->getItemId(), DeliveryEachProductUtil::MODE_FEE);
            if(!isset($v) || is_null($v) || !strlen($v)) continue;

            $prices = soy2_unserialize($v);
            if(!isset($prices[$user->getArea()])) continue;

            $price += (int)$prices[$user->getArea()];
        }

        return $price;
    }
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_each_product", "DeliveryEachProductModule");
