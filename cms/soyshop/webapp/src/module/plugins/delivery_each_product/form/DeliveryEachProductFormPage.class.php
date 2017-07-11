<?php

class DeliveryEachProductFormPage extends WebPage{

    private $configObj;
    private $itemId;

    function __construct(){
        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");
        SOY2::imports("module.plugins.delivery_each_product.component.*");
        SOY2DAOFactory::importEntity("config.SOYShop_Area");
    }

    function execute(){
        WebPage::__construct();

        $this->createAdd("prices", "DeliveryEachProductPriceListComponent", array(
            "list"   => SOYShop_Area::getAreas(),
            "prices" => self::getPrices()
        ));

        SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
        $this->createAdd("default_prices", "DefaultPriceListComponent", array(
          "list" => DeliveryNormalUtil::getPrice()
        ));

        $this->addInput("delivery_email", array(
            "name" => "EachProduct[" . DeliveryEachProductUtil::MODE_EMAIL . "]",
            "value" => DeliveryEachProductUtil::get($this->itemId, DeliveryEachProductUtil::MODE_EMAIL)
        ));

        $this->addLabel("toggle_js", array(
          "html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/toggle.js")
        ));

        $this->addLabel("default_js", array(
          "html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/default.js")
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
