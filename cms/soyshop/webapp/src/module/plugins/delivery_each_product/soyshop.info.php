<?php

class DeliveryEachProductInfo extends SOYShopInfoPageBase{

  function getPage($active = false){
      if($active){
        if(!self::checkInstalledOtherDeliveryModule()){
          return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=delivery_each_product").'">配送料の設定</a>';
        }else{
          return '<p class="alert alert-danger">他の配送モジュールと併用できません。</p>';
        }

      }else{
        return "";
      }
  }

  private function checkInstalledOtherDeliveryModule(){
    try{
      $plugins = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByTypeAndIsActive(SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY, SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }catch(Exception $e){
      return false;
    }
    // 自身の配送モジュール以外を調べるため、1より多いかで判定
    return (count($plugins) > 1);
  }
}
SOYShopPlugin::extension("soyshop.info", "delivery_each_product", "DeliveryEachProductInfo");
