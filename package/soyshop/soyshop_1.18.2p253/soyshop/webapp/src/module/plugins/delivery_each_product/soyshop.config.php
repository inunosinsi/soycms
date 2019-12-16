<?php

class DeliveryEachProductConfig extends SOYShopConfigPageBase{

    /**
     * @return string
     */
    function getConfigPage(){
      SOY2::import("module.plugins.delivery_normal.config.DeliveryNormalConfigFormPage");
      $form = SOY2HTMLFactory::createInstance("DeliveryNormalConfigFormPage");
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
