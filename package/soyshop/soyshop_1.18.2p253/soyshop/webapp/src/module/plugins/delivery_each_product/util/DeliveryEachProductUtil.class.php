<?php

class DeliveryEachProductUtil{

    const MODE_FEE = "fee";
    const MODE_EMAIL = "email";

    const PLUGIN_ID = "deliver_each_product";

    public static function get($itemId, $mode = self::MODE_FEE){
      static $dao;
      if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
      try{
          $config = $dao->get($itemId, self::PLUGIN_ID . "_" . $mode)->getValue();
      }catch(Exception $e){
          $config = null;
      }

      if($mode != self::MODE_FEE) return $config;

      // modeがfeeの場合は初期設定を取得
      if(is_null($config)) {
        $config = array();
      }else if(strlen($config)){
        $config = soy2_unserialize($config);
      }

      if(isset($config[1]) && strlen($config[1]) && is_numeric($config[1])) return soy2_serialize($config);

      SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
      $default = DeliveryNormalUtil::getPrice();
      if(isset($default[1]) && strlen($default[1]) && (int)$default > 0) $config = $default;
      
      return soy2_serialize($config);
    }
}
