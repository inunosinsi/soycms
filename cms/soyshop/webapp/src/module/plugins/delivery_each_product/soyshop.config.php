<?php

class DeliveryEachProductConfig extends SOYShopConfigPageBase{

    /**
     * @return string
     */
    function getConfigPage(){
        return "<p class=\"error always\">他の配送モジュールと併用することはできません。</p>";
    }

    /**
     * @return string
     */
    function getConfigPageTitle(){
        return "商品ごと配送料設定";
    }
}

SOYShopPlugin::extension("soyshop.config","delivery_each_product","DeliveryEachProductConfig");
