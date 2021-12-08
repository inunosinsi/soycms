<?php

class DeliveryEachProductUtil{

    const MODE_FEE = "fee";
    const MODE_EMAIL = "email";

    const PLUGIN_ID = "deliver_each_product";

    public static function get($itemId, $mode = self::MODE_FEE){
      $v = soyshop_get_item_attribute_value($itemId, self::PLUGIN_ID . "_" . $mode, "string");
      if($mode != self::MODE_FEE) return $v;

      // modeがfeeの場合は初期設定を取得
	  $cnf = (strlen($v)) ? soy2_unserialize($v) : array();

      if(isset($cnf[1]) && is_numeric($cnf[1])) return soy2_serialize($cnf);

      SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
      $default = DeliveryNormalUtil::getPrice();
      if(isset($default[1]) && is_numeric($default[1]) && (int)$default > 0) $cnf = $default;

      return soy2_serialize($cnf);
    }
}
