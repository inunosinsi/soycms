<?php
class ItemPriceNoNumberFormat extends SOYShopItemCustomFieldBase{


    function doPost(SOYShop_Item $item){}

    function onOutput($htmlObj, SOYShop_Item $item){

        //表示価格(通常価格、セール設定中はセール価格)
        $htmlObj->addLabel("item_price_no_format", array(
            "text" => (int)$item->getSellingPrice(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));

        //通常価格
        $htmlObj->addLabel("item_normal_price_no_format", array(
            "text" => (int)$item->getPrice(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));

        //セール価格
        $htmlObj->addLabel("item_sale_price_no_format", array(
            "text" => (int)$item->getSalePrice(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));

        //定価
        $htmlObj->addLabel("item_list_price_no_format", array(
            "text" => (int)$item->getAttribute("list_price"),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));
    }

    function getForm(SOYShop_Item $item){}

    function onDelete(int $itemId){}
}
SOYShopPlugin::extension("soyshop.item.customfield", "item_price_no_number_format", "ItemPriceNoNumberFormat");
