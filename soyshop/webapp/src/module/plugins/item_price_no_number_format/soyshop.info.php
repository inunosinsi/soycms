<?php
/*
 */
class ItemPriceNoNumberFormatInfo extends SOYShopInfoPageBase{

    function getPage(bool $active=false){

        if($active){
            return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_price_no_number_format") . '">カンマなし商品価格表示プラグイン</a>';
        }else{
            return "";
        }
    }
}
SOYShopPlugin::extension("soyshop.info", "item_price_no_number_format", "ItemPriceNoNumberFormatInfo");
