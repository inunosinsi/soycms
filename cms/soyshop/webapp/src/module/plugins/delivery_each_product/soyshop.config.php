<?php

class DeliveryEachProductConfig extends SOYShopConfigPageBase{

    /**
     * @return string
     */
    function getConfigPage(){
      SOY2::import("module.plugins.delivery_each_product.config.DeliveryEachProductConfigPage");
      $form = SOY2HTMLFactory::createInstance("DeliveryEachProductConfigPage");
      $form->setConfigObj($this);
      $form->execute();
      return $form->getObject();
    }

    /**
     * @return string
     */
    function getConfigPageTitle(){
        return "商品ごと配送料設定";
    }
}

SOYShopPlugin::extension("soyshop.config","delivery_each_product","DeliveryEachProductConfig");
