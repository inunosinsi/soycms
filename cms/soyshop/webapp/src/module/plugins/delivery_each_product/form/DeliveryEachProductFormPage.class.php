<?php

class DeliveryEachProductFormPage extends WebPage{

    private $configObj;
    private $itemId;

    function __construct(){
        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");
        SOY2DAOFactory::importEntity("config.SOYShop_Area");
    }

    function execute(){
        WebPage::__construct();

        SOY2::import("module.plugins.delivery_each_product.component.DeliveryEachProductPriceListComponent");
        $this->createAdd("prices", "DeliveryEachProductPriceListComponent", array(
            "list"   => SOYShop_Area::getAreas(),
            "prices" => self::getPrices()
        ));

        $this->addInput("delivery_email", array(
            "name" => "EachProduct[" . DeliveryEachProductUtil::MODE_EMAIL . "]",
            "value" => DeliveryEachProductUtil::get($this->itemId, DeliveryEachProductUtil::MODE_EMAIL)
        ));
    }

    private function getPrices(){
        $v = DeliveryEachProductUtil::get($this->itemId, DeliveryEachProductUtil::MODE_FEE);
        if(!isset($v) || is_null($v) || !strlen($v)) return array();

        return soy2_unserialize($v);
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }

    function setItemId($itemId){
        $this->itemId = $itemId;
    }
}
