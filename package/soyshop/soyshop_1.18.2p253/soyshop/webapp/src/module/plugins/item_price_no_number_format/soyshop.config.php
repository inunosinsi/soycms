<?php
class ItemPriceNoNumberFormatConfig extends SOYShopConfigPageBase{

    /**
     * @return string
     */
    function getConfigPage(){

        SOY2::import("module.plugins.item_price_no_number_format.config.ItemPriceNoNumberFormatConfigPage");
        $form = SOY2HTMLFactory::createInstance("ItemPriceNoNumberFormatConfigPage");
        $form->setConfigObj($this);
        $form->execute();
        return $form->getObject();
    }

    /**
     * @return string
     * 拡張設定に表示されたモジュールのタイトルを表示する
     */
    function getConfigPageTitle(){
        return "カンマなし商品価格表示プラグイン";
    }

}
SOYShopPlugin::extension("soyshop.config", "item_price_no_number_format", "ItemPriceNoNumberFormatConfig");
